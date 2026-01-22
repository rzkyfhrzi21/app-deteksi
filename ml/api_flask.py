import os
os.environ["TF_CPP_MIN_LOG_LEVEL"] = "3"
os.environ["CUDA_VISIBLE_DEVICES"] = "-1"

# =====================================
# MODE APLIKASI
# =====================================
# MODE = "local"   → untuk testing localhost
# MODE = "online"  → untuk server (gunicorn)
MODE = os.getenv("FLASK_MODE", "online")

# Sembunyikan warning TensorFlow
os.environ["TF_CPP_MIN_LOG_LEVEL"] = "2"

from flask import Flask, request, jsonify
import numpy as np
import tensorflow as tf
import cv2

# ==========================
# KONFIGURASI DASAR
# ==========================

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODEL_PATH = os.path.join(BASE_DIR, "model.h5")

IMG_SIZE = (128, 128)

CLASS_NAMES = [
    "Bacterialblight",
    "Blast",
    "Brownspot",
    "Tungro"
]

# ==========================
# LOAD MODEL SEKALI SAJA
# ==========================

print(f"[INFO] Loading model from: {MODEL_PATH}")
model = tf.keras.models.load_model(MODEL_PATH)
print("[INFO] Model loaded successfully.")

# ==========================
# PREPROCESS IMAGE
# ==========================

def preprocess_image(image_bytes):
    file_bytes = np.frombuffer(image_bytes, np.uint8)
    img = cv2.imdecode(file_bytes, cv2.IMREAD_COLOR)

    if img is None:
        raise ValueError("Gambar tidak valid (JPG/PNG saja).")

    img = cv2.resize(img, IMG_SIZE)
    img = img.astype("float32") / 255.0
    img = np.expand_dims(img, axis=0)

    return img

# ==========================
# FLASK APP
# ==========================

app = Flask(__name__)

@app.route("/health", methods=["GET"])
def health():
    return jsonify({
        "status": "ok",
        "mode": MODE,
        "message": "API is running"
    }), 200


@app.route("/predict", methods=["POST"])
def predict():
    if "image" not in request.files:
        return jsonify({"error": "Field 'image' tidak ditemukan."}), 400

    file = request.files["image"]

    if file.filename == "":
        return jsonify({"error": "Nama file kosong."}), 400

    try:
        img = preprocess_image(file.read())
        preds = model.predict(img)[0]

        idx = int(np.argmax(preds))
        confidence = float(preds[idx])

        return jsonify({
            "label": CLASS_NAMES[idx],
            "confidence": confidence,
            "probs": {
                CLASS_NAMES[i]: float(p)
                for i, p in enumerate(preds)
            }
        }), 200

    except Exception as e:
        print("[ERROR]", str(e))
        return jsonify({"error": str(e)}), 500


# ==========================
# JALANKAN SERVER
# ==========================

if __name__ == "__main__":

    # ===============================
    # MODE LOCAL (UNCOMMENT JIKA TEST)
    # ===============================
    if MODE == "local":
        print("[INFO] Running in LOCAL mode")
        app.run(
            host="127.0.0.1",
            port=5000,
            debug=True
        )

    # ==================================
    # MODE ONLINE (GUNICORN AKAN HANDLE)
    # ==================================
    else:
        print("[INFO] Running in ONLINE mode via Gunicorn")
