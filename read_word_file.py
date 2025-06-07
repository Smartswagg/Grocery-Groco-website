from docx import Document
import re

def extract_section(filename, section_title):
    doc = Document(filename)
    section_lines = []
    capture = False
    pattern = re.compile(rf"^\s*{re.escape(section_title)}[\s\S]*", re.IGNORECASE)
    next_section_pattern = re.compile(r"^\s*\d+\.\d+\s+")
    for para in doc.paragraphs:
        text = para.text.strip()
        if not capture and pattern.match(text):
            capture = True
            section_lines.append(text)
            continue
        if capture:
            if next_section_pattern.match(text) and not pattern.match(text):
                break
            section_lines.append(text)
    return '\n'.join(section_lines)

if __name__ == "__main__":
    file_path = "GROCO REPORT- 4.docx"
    section_title = "1.1 Introduction"
    content = extract_section(file_path, section_title)
    print(content if content else f"Section '{section_title}' not found.")
