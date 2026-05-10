import sys
import json
import os
import google.generativeai as genai
from PyPDF2 import PdfReader

# 1. Setup a Pool of API Keys (Must be from DIFFERENT projects to work)
API_KEYS = [
    "AIzaSyArN0RS0Q_4aO79uUP3Um43nX9u7BTqVdw", # Key 1
    "AIzaSyAoVbxRnYVJdy8o066H8MJ_vu6AEHASigg"
]

MODELS_TO_TRY = [
    'models/gemini-3.1-flash-lite-preview', # 500 RPD (Your Workhorse)
    'models/gemini-3-flash-preview',      # 20 RPD (Smart & Fast)
    'models/gemini-2.5-flash',             # 20 RPD (Reliable Fallback)
    'models/gemini-2.5-flash-lite'         # 20 RPD (Extra Safety)
]

def extract_text_from_pdf(pdf_path):
    try:
        reader = PdfReader(pdf_path)
        return "".join([page.extract_text() for page in reader.pages])
    except Exception as e:
        return f"Error reading PDF: {str(e)}"

def analyze_with_ai(cv_text, job_desc):
    prompt = f"Analyze Resume: {cv_text} against Job: {job_desc}. Return ONLY JSON with 'score' and 'summary'."
    
    last_error = ""

    # OUTER LOOP: Cycle through your Keys
    for current_key in API_KEYS:
        try:
            genai.configure(api_key=current_key)
            
            # INNER LOOP: Cycle through the Models for this specific key
            for model_name in MODELS_TO_TRY:
                try:
                    model = genai.GenerativeModel(model_name)
                    response = model.generate_content(prompt)
                    
                    text_content = response.text.strip()
                    if "```json" in text_content:
                        text_content = text_content.split("```json")[1].split("```")[0]
                    elif "```" in text_content:
                        text_content = text_content.split("```")[1].split("```")[0]
                    
                    return json.loads(text_content.strip())
                
                except Exception as model_err:
                    last_error = str(model_err)
                    # If it's a 429, it might be just this model or the whole key
                    if "429" in last_error:
                        continue # Try next model
                    else:
                        break # Other errors (like 404) mean this model is dead
            
            # If we finished the inner loop and still have a 429, 
            # it means this KEY is exhausted. Move to next key.
            if "429" in last_error:
                continue 

        except Exception as key_err:
            last_error = str(key_err)
            continue

    raise Exception(f"All keys and models exhausted. Last error: {last_error}")

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print(json.dumps({"score": 0, "summary": "Missing arguments"}))
        sys.exit(1)

    resume_text = extract_text_from_pdf(sys.argv[1])
    try:
        result = analyze_with_ai(resume_text, sys.argv[2])
        print(json.dumps(result))
    except Exception as e:
        print(json.dumps({"score": 0, "summary": f"AI Error: {str(e)}"}))