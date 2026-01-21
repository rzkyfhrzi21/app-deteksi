import os
import io

# SEMBUNYIKAN LOG INFO/WARNING DARI TENSORFLOW
os.environ["TF_CPP_MIN_LOG_LEVEL"] = "2"

from flask import Flask, request, jsonify
import numpy as np
import tensorflow as tf
import cv2

# ==========================
# KONFIGURASI DASAR
# ==========================

# Lokasi file model.h5 (relatif terhadap file ini)
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODEL_PATH = os.path.join(BASE_DIR, "model.h5")

# Ukuran input gambar (harus sama dengan train_model.py)
IMG_SIZE = (128, 128)

# Urutan kelas harus sama dengan CLASS_NAMES di train_model.py
CLASS_NAMES = ['Bacterialblight', 'Blast', 'Brownspot', 'Tungro']

# ==========================
# LOAD MODEL SEKALI DI AWAL
# ==========================

print(f"[INFO] Loading model from: {MODEL_PATH}")
model = tf.keras.models.load_model(MODEL_PATH)
print("[INFO] Model loaded successfully.")

# ==========================
# FUNGSI PREPROCESS
# ==========================

def preprocess_image(image_bytes):
    """
    Menerima bytes gambar, mengembalikan array siap-prediksi:
    shape: (1, 128, 128, 3), float32 [0,1]
    """
    # Baca bytes jadi array numpy
    file_bytes = np.frombuffer(image_bytes, np.uint8)
    img = cv2.imdecode(file_bytes, cv2.IMREAD_COLOR)  # BGR

    if img is None:
        raise ValueError("Gambar tidak dapat dibaca. Pastikan format file benar (JPG/PNG).")

    # Resize ke IMG_SIZE
    img = cv2.resize(img, IMG_SIZE)

    # Konversi ke float dan normalisasi ke [0,1]
    img = img.astype("float32") / 255.0

    # Tambah dimensi batch -> (1, 128, 128, 3)
    img = np.expand_dims(img, axis=0)
    return img

# ==========================
# FLASK APP
# ==========================

app = Flask(__name__)

@app.route("/health", methods=["GET"])
def health():
    """Endpoint sederhana untuk cek server hidup."""
    return jsonify({"status": "ok", "message": "API is running"}), 200


@app.route("/predict", methods=["POST"])
def predict():
    """
    Terima file gambar (field name: 'image'),
    kembalikan JSON: {label, confidence, probs}
    """
    if "image" not in request.files:
        return jsonify({"error": "Field 'image' tidak ditemukan di request."}), 400

    file = request.files["image"]

    if file.filename == "":
        return jsonify({"error": "Nama file kosong."}), 400

    try:
        image_bytes = file.read()
        img = preprocess_image(image_bytes)

        # Prediksi
        preds = model.predict(img)[0]  # shape: (num_classes,)
        idx = int(np.argmax(preds))
        confidence = float(preds[idx])

        # Susun respon
        response = {
            "label": CLASS_NAMES[idx],
            "confidence": confidence,
            "probs": {
                CLASS_NAMES[i]: float(p) for i, p in enumerate(preds)
            }
        }
        return jsonify(response), 200

    except Exception as e:
        # Untuk debugging, bisa print(e) ke console
        print("[ERROR] Exception in /predict:", str(e))
        return jsonify({"error": str(e)}), 500


if __name__ == "__main__":
    # Jalankan di localhost:5000
    app.run(host="127.0.0.1", port=5000, debug=True)
