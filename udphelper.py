# udphelper.py
import subprocess
from flask import Flask, request, jsonify
from flask_cors import CORS
import shutil
import os

app = Flask(__name__)
CORS(app)

# Try to find VLC automatically; fallback to /usr/bin/vlc
VLC_PATH = shutil.which("vlc") or "/usr/bin/vlc"

@app.route("/")
def launch():
    url = request.args.get("url")
    if not url:
        return jsonify({"error": "No URL provided"}), 400

    if not os.path.exists(VLC_PATH):
        return jsonify({"error": f"VLC not found at {VLC_PATH}"}), 500

    try:
        # Small window 320x240 at position (100,100)
        subprocess.Popen([
            VLC_PATH,
            url,
            "--video-x=100",
            "--video-y=100",
            "--width=320",
            "--height=240",
            "--no-video-title-show",
            "--quiet"
        ])
        return jsonify({"status": f"Player launched for {url}"})
    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == "__main__":
    # Run on all interfaces at port 5000 (your browser calls http://127.0.0.1:5000)
    app.run(host="0.0.0.0", port=5000)
