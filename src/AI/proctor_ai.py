import cv2
import numpy as np
import base64
import os
from fastapi import FastAPI, Body
from fastapi.middleware.cors import CORSMiddleware

app = FastAPI()

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"], 
    allow_methods=["*"],
    allow_headers=["*"],
)

# --- SMART PATH CHECK ---
# Try to find the built-in path, otherwise look in the current folder
xml_name = 'haarcascade_frontalface_default.xml'
cascade_path = os.path.join(cv2.data.haarcascades, xml_name)

if not os.path.exists(cascade_path):
    cascade_path = xml_name # Look in the same folder as this script

face_cascade = cv2.CascadeClassifier(cascade_path)

@app.post("/analyze-frame")
async def analyze_frame(data: dict = Body(...)):
    try:
        if face_cascade.empty():
            return {"status": "error", "message": "Model file not found"}

        header, encoded = data['image'].split(",", 1)
        nparr = np.frombuffer(base64.b64decode(encoded), np.uint8)
        img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        faces = face_cascade.detectMultiScale(gray, 1.1, 4)
        
        face_count = len(faces)
        status = "normal"
        
        if face_count == 0:
            status = "missing"
        elif face_count > 1:
            status = "multiple"

        return {
            "status": status,
            "face_count": face_count,
            "message": f"Detected {face_count} face(s)"
        }
    except Exception as e:
        return {"status": "error", "message": str(e)}

if __name__ == "__main__":
    import uvicorn
    # host="0.0.0.0" works for BOTH localhost and Digital Ocean.
    uvicorn.run(app, host="0.0.0.0", port=8000)