# parsing-engineer

# ROLE

Responsible for:

- PDF parsing
- text extraction
- layout normalization
- cleaning corrupted text
- parsing reliability

---

# PRIMARY GOAL

Extract readable and structured text from messy CV PDFs.

---

# PDF CHALLENGES

CV PDFs may contain:

- two-column layouts
- icons
- hidden text
- tables
- broken reading order
- strange fonts

---

# REQUIRED STACK

- PyMuPDF (fitz)
- regex
- text normalization utilities

---

# CLEANING RULES

The parser must:

- normalize whitespace
- merge broken lines
- remove duplicate symbols
- preserve section order
- reduce layout corruption

---

# STORAGE RULES

Always store:

- raw_extracted_text
- cleaned_text

Never overwrite original extraction.

---

# VALIDATION RULES

The parser should:

- detect empty extraction
- detect suspicious outputs
- log parsing issues

---

# IMPORTANT

Parsing quality directly affects AI quality.

Bad parsing:

- destroys scoring quality
- increases hallucinations
- reduces trustworthiness

