import os
import time
import cv2
import numpy as np
import pandas as pd
import matplotlib.pyplot as plt

from sklearn.model_selection import train_test_split
from sklearn.preprocessing import MinMaxScaler

from tensorflow.keras.models import Sequential
from tensorflow.keras.layers import Conv2D, MaxPooling2D, Flatten, Dense, Dropout, Input
from tensorflow.keras.optimizers import SGD
from tensorflow.keras.preprocessing.image import ImageDataGenerator
from tensorflow.keras.callbacks import EarlyStopping, Callback
from tensorflow.keras.utils import to_categorical

# ==============================
# KONFIGURASI DASAR
# ==============================

DATASET_DIR = "dataset"  # folder dataset relatif dari file ini
CLASS_NAMES = ['Bacterialblight', 'Blast', 'Brownspot', 'Tungro']
classes = {name: idx for idx, name in enumerate(CLASS_NAMES)}
inverted_classes = {idx: name for name, idx in classes.items()}

IMG_SIZE = (128, 128)  # harus sama dengan saat inferensi
LOG_PATH = "log_training.txt"  # file log training

# ==============================
# CALLBACK: PROGRESS BAR
# ==============================

class TrainingProgress(Callback):
    """
    Callback untuk menampilkan progress bar di terminal
    dengan persentase dan estimasi waktu (ETA).
    """
    def __init__(self, total_epochs, steps_per_epoch):
        super().__init__()
        self.total_epochs = total_epochs
        self.epoch_times = []
        self.epoch_start_time = None
        self.steps_per_epoch = steps_per_epoch

    def on_train_begin(self, logs=None):
        total_steps = self.total_epochs * self.steps_per_epoch
        print(f"\n[INFO] Training dimulai.")
        print(f"[INFO] Target epochs      : {self.total_epochs}")
        print(f"[INFO] Steps per epoch    : {self.steps_per_epoch}")
        print(f"[INFO] Total perkiraan step: {total_steps}")
        print(f"[INFO] ETA waktu akan lebih akurat setelah 1–2 epoch pertama.\n")

    def on_epoch_begin(self, epoch, logs=None):
        self.epoch_start_time = time.time()

    def on_epoch_end(self, epoch, logs=None):
        epoch_time = time.time() - self.epoch_start_time
        self.epoch_times.append(epoch_time)

        avg_epoch = sum(self.epoch_times) / len(self.epoch_times)
        done_epochs = epoch + 1
        remaining_epochs = max(0, self.total_epochs - done_epochs)
        eta_seconds = remaining_epochs * avg_epoch

        percent = done_epochs / self.total_epochs * 100.0

        bar_len = 30
        filled_len = int(bar_len * done_epochs / self.total_epochs)
        bar = "█" * filled_len + "-" * (bar_len - filled_len)

        print(
            f"\r[{bar}] {percent:5.1f}% | "
            f"epoch {done_epochs:3d}/{self.total_epochs} | "
            f"last: {epoch_time:5.1f}s | "
            f"ETA: {eta_seconds/60:5.1f} min",
            end=""
        )

        if done_epochs == self.total_epochs:
            print("\n")


# ==============================
# CALLBACK: LOG TRAINING KE FILE
# ==============================

class TrainingLogger(Callback):
    """
    Callback untuk menulis log training ke log_training.txt
    Format: epoch,loss,accuracy,val_loss,val_accuracy,epoch_time,elapsed_time
    """
    def __init__(self, log_path):
        super().__init__()
        self.log_path = log_path
        self.start_time = None
        self.epoch_start_time = None

    def on_train_begin(self, logs=None):
        self.start_time = time.time()
        with open(self.log_path, "w", encoding="utf-8") as f:
            f.write("=== LOG TRAINING CNN DAUN PADI ===\n")
            f.write(f"CLASS_NAMES: {CLASS_NAMES}\n")
            f.write(f"IMG_SIZE   : {IMG_SIZE}\n")
            f.write(f"Start time : {time.ctime(self.start_time)}\n")
            f.write("epoch,loss,accuracy,val_loss,val_accuracy,epoch_time(s),elapsed_time(s)\n")

    def on_epoch_begin(self, epoch, logs=None):
        self.epoch_start_time = time.time()

    def on_epoch_end(self, epoch, logs=None):
        end_time = time.time()
        epoch_time = end_time - self.epoch_start_time
        elapsed = end_time - self.start_time

        loss = logs.get("loss", None)
        acc = logs.get("accuracy", logs.get("acc", None))
        val_loss = logs.get("val_loss", None)
        val_acc = logs.get("val_accuracy", logs.get("val_acc", None))

        with open(self.log_path, "a", encoding="utf-8") as f:
            f.write(
                f"{epoch+1},"
                f"{loss if loss is not None else ''},"
                f"{acc if acc is not None else ''},"
                f"{val_loss if val_loss is not None else ''},"
                f"{val_acc if val_acc is not None else ''},"
                f"{epoch_time:.4f},"
                f"{elapsed:.4f}\n"
            )

    def on_train_end(self, logs=None):
        end_time = time.time()
        total = end_time - self.start_time
        with open(self.log_path, "a", encoding="utf-8") as f:
            f.write(f"\nTotal training time (s): {total:.4f}\n")
            f.write(f"End time: {time.ctime(end_time)}\n")


# ==============================
# FUNGSI BACA & PERSIAPAN DATA
# ==============================

def load_and_resize_image(file_path, target_shape=IMG_SIZE):
    image = cv2.imread(file_path)
    if image is None:
        return None
    resized_image = cv2.resize(image, target_shape)
    return resized_image


def load_image_class_by_directory(image_dir):
    image_files = os.listdir(image_dir)
    images = []
    for file in image_files:
        if file.lower().endswith(('.jpg', '.jpeg', '.png')):
            image_path = os.path.join(image_dir, file)
            resized_image = load_and_resize_image(image_path)
            if resized_image is not None:
                images.append(resized_image)

    if len(images) == 0:
        raise ValueError(f"Tidak ada gambar valid di folder: {image_dir}")

    print(f"[{os.path.basename(image_dir)}] Jumlah gambar tersedia: {len(images)}")
    return images


def limit_images(images, limit):
    """
    Batasi jumlah gambar per class.
    Jika limit <= 0 → pakai semua.
    """
    if limit <= 0 or limit >= len(images):
        return images
    return images[:limit]


def flatten_images(images):
    data_flattened = []
    for image in images:
        flattened_image = image.reshape(-1)
        data_flattened.append(flattened_image)

    print(f"Jumlah gambar (flattened): {len(data_flattened)}")
    print(f"Shape satu gambar setelah flatten: {data_flattened[0].shape}")
    return np.array(data_flattened)


def assign_image_class_label(images, class_label: int):
    data_flattened = flatten_images(images)
    data_labeled = []
    for image in data_flattened:
        data_labeled.append(np.concatenate([image, [class_label]]))

    print(f"Jumlah data dengan label: {len(data_labeled)}")
    print(f"Shape satu data + label: {data_labeled[0].shape} --- label: {class_label}\n")
    return np.array(data_labeled)


def concat_arrays_to_dataframe(arrays):
    dataset = np.concatenate(arrays, axis=0)
    num_pix = dataset.shape[1] - 1  # kolom terakhir = label

    col_lst = [f"pixel{col}" for col in range(num_pix)]
    col_lst.append("label")

    df_dataset = pd.DataFrame(dataset, columns=col_lst)
    return df_dataset


def split_train_test_files(images_lst_lst, num_test_set: int):
    """
    Bagi gambar per kelas jadi train & test.
    num_test_set = berapa gambar per kelas yang disisihkan sebagai test.
    """
    train_images_lst_lst = []
    test_images_lst_lst = []
    for images in images_lst_lst:
        test_set = images[:num_test_set]
        train_set = images[num_test_set:]
        train_images_lst_lst.append(train_set)
        test_images_lst_lst.append(test_set)
    return train_images_lst_lst, test_images_lst_lst


# ==============================
# MAIN TRAINING PIPELINE
# ==============================

if __name__ == "__main__":
    print("=== LOAD DATASET ===")

    # Input jumlah gambar per kelas
    try:
        limit_input = int(input("Masukkan jumlah gambar per class (0 = pakai semua): ").strip())
    except Exception:
        limit_input = 0

    images_by_class = {}
    for class_name in CLASS_NAMES:
        dir_path = os.path.join(DATASET_DIR, class_name)
        if not os.path.isdir(dir_path):
            raise FileNotFoundError(f"Folder kelas tidak ditemukan: {dir_path}")

        print(f"\n[LOAD] Kelas: {class_name}")
        imgs = load_image_class_by_directory(dir_path)
        imgs = limit_images(imgs, limit_input)
        images_by_class[class_name] = imgs
        print(f"[INFO] Dipakai untuk training: {len(imgs)} gambar")

    classes_dict = {name: len(imgs) for name, imgs in images_by_class.items()}
    print("\n[INFO] Rekap jumlah gambar per kelas yang dipakai:")
    for k, v in classes_dict.items():
        print(f"  - {k}: {v}")

    # Plot (opsional)
    try:
        plt.bar(*zip(*classes_dict.items()))
        plt.title("Jumlah gambar per kelas (dipakai training)")
        plt.show()
    except Exception as e:
        print("[WARN] Gagal menampilkan plot jumlah gambar:", e)

    # Susun list gambar sesuai urutan CLASS_NAMES
    images_lst_lst = [images_by_class[name] for name in CLASS_NAMES]

    # Bagi train/test
    num_test_set = 20  # gambar per kelas untuk test (tidak ikut training)
    train_images, test_images = split_train_test_files(images_lst_lst, num_test_set)

    # Susun train set + label ke DataFrame
    images_labeled_arrays = []
    for idx, images in enumerate(train_images):
        labeled = assign_image_class_label(images, idx)
        images_labeled_arrays.append(labeled)

    df_images = concat_arrays_to_dataframe(images_labeled_arrays)
    print("\nHead df_images:")
    print(df_images.head())

    # Split train / val
    X_images = df_images.drop("label", axis=1)
    y_images = df_images["label"]

    X_train, X_val, y_train, y_val = train_test_split(
        X_images,
        y_images,
        test_size=0.25,
        random_state=2,
        shuffle=True
    )

    print("\n[INFO] Shape train X:", X_train.shape)
    print("[INFO] Shape train y:", y_train.shape)
    print("[INFO] Shape val   X:", X_val.shape)
    print("[INFO] Shape val   y:", y_val.shape)

    # Normalisasi [0,255] → [0,1]
    scaler = MinMaxScaler(feature_range=(0, 1))
    scaler.fit(np.array(X_train))

    X_train_np = scaler.transform(np.array(X_train))
    X_val_np = scaler.transform(np.array(X_val))

    # reshape ke (H, W, C)
    X_train_RGB = X_train_np.reshape(-1, IMG_SIZE[0], IMG_SIZE[1], 3)
    X_val_RGB = X_val_np.reshape(-1, IMG_SIZE[0], IMG_SIZE[1], 3)

    y_train = y_train.values.reshape(-1, 1)
    y_val = y_val.values.reshape(-1, 1)

    print("\n[INFO] Shape train X RGB:", X_train_RGB.shape)
    print("[INFO] Shape train y    :", y_train.shape)
    print("[INFO] Shape val   X RGB:", X_val_RGB.shape)
    print("[INFO] Shape val   y    :", y_val.shape)

    # ==============================
    # BANGUN MODEL
    # ==============================

    input_shape = X_train_RGB[0].shape
    num_train_images = len(X_train_RGB)
    num_classes = len(CLASS_NAMES)

    print(f"\n[INFO] Input shape        : {input_shape}")
    print(f"[INFO] Jumlah train images: {num_train_images}")
    print(f"[INFO] Jumlah kelas       : {num_classes}")

    model = Sequential()
    model.add(Input(shape=input_shape))

    model.add(Conv2D(128, (3, 3), activation="relu"))
    model.add(MaxPooling2D((2, 2)))
    model.add(Dropout(0.5))

    model.add(Conv2D(64, (3, 3), activation="relu"))
    model.add(MaxPooling2D((2, 2)))
    model.add(Dropout(0.5))

    model.add(Flatten())
    model.add(Dense(256, activation="relu"))
    model.add(Dropout(0.5))
    model.add(Dense(num_classes, activation="softmax"))

    opt = SGD(learning_rate=0.0001, momentum=0.9)
    model.compile(loss="categorical_crossentropy", optimizer=opt, metrics=["accuracy"])

    model.summary()

    # ==============================
    # TRAINING DENGAN CALLBACK
    # ==============================

    epochs = 100
    batch_size = 64

    train_datagen = ImageDataGenerator(
        rotation_range=10,
        width_shift_range=0.1,
        height_shift_range=0.1,
        zoom_range=0.1
    )

    monitor_val_loss = EarlyStopping(
        monitor="val_loss",
        min_delta=1e-3,
        patience=20,
        verbose=1,
        mode="auto",
        restore_best_weights=True
    )

    training_data = train_datagen.flow(
        X_train_RGB,
        to_categorical(y_train, num_classes=num_classes),
        batch_size=batch_size
    )

    validation_data = (
        X_val_RGB,
        to_categorical(y_val, num_classes=num_classes)
    )

    # gunakan panjang generator sebagai steps_per_epoch supaya tidak "ran out of data"
    steps_per_epoch = len(training_data)

    progress_cb = TrainingProgress(total_epochs=epochs, steps_per_epoch=steps_per_epoch)
    logger_cb = TrainingLogger(LOG_PATH)

    history = model.fit(
        training_data,
        epochs=epochs,
        steps_per_epoch=steps_per_epoch,
        batch_size=batch_size,
        validation_data=validation_data,
        callbacks=[monitor_val_loss, progress_cb, logger_cb],
        verbose=0  # pakai progress bar custom, bukan bawaan Keras
    )

    # ==============================
    # SIMPAN MODEL
    # ==============================

    model.save("model.h5")
    print("\n[INFO] Training selesai. Model tersimpan sebagai model.h5")
    print(f"[INFO] Log training tersimpan di {LOG_PATH}")
