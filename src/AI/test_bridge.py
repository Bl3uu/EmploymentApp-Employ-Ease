import sys
import json

# Just echo back what it receives
data = {
    "status": "success",
    "received_path": sys.argv[1] if len(sys.argv) > 1 else "no path",
    "message": "Python is talking to PHP!"
}
print(json.dumps(data))