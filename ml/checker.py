import os
import sys
import importlib
from pathlib import Path

# ======================================
# KONFIGURASI DASAR
# ======================================

BASE_DIR = Path(__file__).resolve().parent
DATASET_DIR = BASE_DIR / "dataset"
MODEL_PATH = BASE_DIR / "model.h5"
CLASS_NAMES = ['Bacterialblight', 'Blast', 'Brownspot', 'Tungro']

REQUIRED_PACKAGES = [
    ("tensorflow", "tensorflow"),
    ("numpy", "numpy"),
    ("pandas", "pandas"),
    ("sklearn", "scikit-learn"),
    ("cv2", "opencv-python"),
    ("matplotlib", "matplotlib"),
]

IMAGE_EXTS = (".jpg", ".jpeg", ".png")

# ======================================
# UTIL ANSI COLOR (BIAR LEBIH JELAS)
# ======================================

class C:
    OK = "\033[92m"
    WARN = "\033[93m"
    FAIL = "\033[91m"
    INFO = "\033[94m"
    END = "\033[0m"

def cprint(msg, color):
    print(color + msg + C.END)

# ======================================
# CEK PYTHON & PACKAGES
# ======================================

def check_python():
    cprint("=== CEK PYTHON ===", C.INFO)
    ver = sys.version.split("\n")[0]
    print(f"Python version : {ver}")
    if sys.version_info < (3, 9):
        cprint("WARNING: Disarankan pakai Python 3.9+ untuk TensorFlow modern.", C.WARN)
    print("")


def check_packages():
    cprint("=== CEK LIBRARY PYTHON ===", C.INFO)
    missing = []
    for mod_name, pip_name in REQUIRED_PACKAGES:
        try:
            importlib.import_module(mod_name)
            cprint(f"[OK]  {mod_name} (pip: {pip_name}) terinstall", C.OK)
        except ImportError:
            cprint(f"[ERR] {mod_name} (pip: {pip_name}) TIDAK TERINSTALL", C.FAIL)
            missing.append((mod_name, pip_name))

    if missing:
        print("")
        cprint("Beberapa library belum terinstall. Jalankan perintah berikut:", C.WARN)
        for _, pip_name in missing:
            print(f"  pip install {pip_name}")
    else:
        cprint("\nSemua library wajib sudah terinstall. ✅", C.OK)

    print("")
    return missing

# ======================================
# CEK TENSORFLOW & GPU
# ======================================

def check_tensorflow_details():
    try:
        import tensorflow as tf
    except ImportError:
        cprint("TensorFlow belum terinstall, skip cek GPU.", C.WARN)
        return

    cprint("=== INFO TENSORFLOW ===", C.INFO)
    print("TensorFlow version :", tf.__version__)

    try:
        gpus = tf.config.list_physical_devices("GPU")
        if gpus:
            cprint(f"[OK] GPU terdeteksi oleh TensorFlow: {gpus}", C.OK)
        else:
            cprint("[INFO] Tidak ada GPU terdeteksi, training akan berjalan di CPU.", C.WARN)
    except Exception as e:
        cprint(f"[WARN] Gagal cek GPU: {e}", C.WARN)
    print("")

# ======================================
# CEK DATASET
# ======================================

def check_dataset():
    cprint("=== CEK DATASET ===", C.INFO)

    if not DATASET_DIR.exists():
        cprint(f"[ERR] Folder dataset tidak ditemukan: {DATASET_DIR}", C.FAIL)
        print("Pastikan struktur seperti:")
        print("  ml/")
        print("    dataset/")
        print("      Bacterialblight/")
        print("      Blast/")
        print("      Brownspot/")
        print("      Tungro/")
        print("")
        return False

    all_good = True
    total_images = 0

    for cls in CLASS_NAMES:
        cls_dir = DATASET_DIR / cls
        if not cls_dir.exists():
            cprint(f"[ERR] Folder kelas '{cls}' tidak ditemukan di {DATASET_DIR}", C.FAIL)
            all_good = False
            continue

        # hitung gambar
        count = 0
        for f in cls_dir.iterdir():
            if f.is_file() and f.suffix.lower() in IMAGE_EXTS:
                count += 1

        total_images += count

        if count == 0:
            cprint(f"[ERR] Kelas '{cls}' tidak punya gambar (.jpg/.jpeg/.png)!", C.FAIL)
            all_good = False
        else:
            cprint(f"[OK]  Kelas '{cls}': {count} gambar", C.OK)

    print("")
    if all_good:
        cprint(f"TOTAL SEMUA GAMBAR: {total_images}", C.OK)
        if total_images < 100:
            cprint("WARNING: Dataset sangat sedikit, model mungkin kurang akurat.", C.WARN)
    else:
        cprint("Ada masalah dengan struktur / isi dataset. Periksa pesan error di atas.", C.FAIL)

    print("")
    return all_good

# ======================================
# CEK train_model.py
# ======================================

def check_train_script():
    cprint("=== CEK train_model.py ===", C.INFO)
    train_path = BASE_DIR / "train_model.py"
    if train_path.exists():
        cprint(f"[OK]  File train_model.py ditemukan di {train_path}", C.OK)
    else:
        cprint(f"[ERR] train_model.py TIDAK ditemukan di {BASE_DIR}", C.FAIL)
    print("")

# ======================================
# CEK model.h5
# ======================================

def check_model_file():
    cprint("=== CEK model.h5 ===", C.INFO)

    if not MODEL_PATH.exists():
        cprint(f"[WARN] model.h5 belum ditemukan di {MODEL_PATH}", C.WARN)
        print("  → Jalankan dulu training: python train_model.py")
        print("")
        return False

    # Info ukuran & waktu modifikasi
    size_bytes = MODEL_PATH.stat().st_size
    mtime = MODEL_PATH.stat().st_mtime

    size_mb = size_bytes / (1024 * 1024)
    from datetime import datetime
    last_mod = datetime.fromtimestamp(mtime).strftime("%Y-%m-%d %H:%M:%S")

    cprint(f"[OK]  model.h5 ditemukan.", C.OK)
    print(f"  Lokasi      : {MODEL_PATH}")
    print(f"  Ukuran      : {size_mb:.2f} MB")
    print(f"  Last update : {last_mod}")

    # Opsional: cek bisa di-load TensorFlow
    try:
        import tensorflow as tf
        _ = tf.keras.models.load_model(str(MODEL_PATH))
        cprint("  [OK] Model berhasil di-load oleh TensorFlow (struktur valid).", C.OK)
    except ImportError:
        cprint("  [WARN] TensorFlow belum terinstall, tidak cek load model.", C.WARN)
    except Exception as e:
        cprint(f"  [ERR] Gagal load model.h5: {e}", C.FAIL)

    print("")
    return True

# ======================================
# MAIN
# ======================================

if __name__ == "__main__":
    print("===========================================")
    print("   CHECKER SISTEM TRAINING CNN DAUN PADI   ")
    print("===========================================\n")

    check_python()
    missing = check_packages()
    if not missing:
        check_tensorflow_details()

    dataset_ok = check_dataset()
    check_train_script()
    model_ok = check_model_file()

    print("Selesai cek.\n")

    if missing:
        cprint("STATUS AKHIR: Ada library yang belum terinstall.", C.FAIL)
    elif not dataset_ok:
        cprint("STATUS AKHIR: Ada masalah pada dataset.", C.FAIL)
    elif not model_ok:
        cprint("STATUS AKHIR: model.h5 belum siap.", C.WARN)
    else:
        cprint("STATUS AKHIR: Lingkungan & model SIAP digunakan. ✅", C.OK)
