import re


class CvTextCleaner:
    _control_chars = re.compile(r"[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]")
    _decorative_chars = re.compile(r"[•●◦▪■□◆◇★☆✓✔✕✖➤➔→←↑↓]+")
    _repeated_symbols = re.compile(r"([_=*#~\-])\1{2,}")
    _spaces = re.compile(r"[ \t]+")
    _blank_lines = re.compile(r"\n{3,}")

    def clean(self, raw_text: str) -> str:
        text = raw_text.replace("\r\n", "\n").replace("\r", "\n")
        text = self._control_chars.sub("", text)
        text = self._decorative_chars.sub(" ", text)
        text = self._repeated_symbols.sub(r"\1", text)

        lines = [self._normalize_line(line) for line in text.split("\n")]
        lines = self._merge_broken_lines(lines)

        cleaned = "\n".join(lines)
        cleaned = self._spaces.sub(" ", cleaned)
        cleaned = self._blank_lines.sub("\n\n", cleaned)

        return cleaned.strip()

    def _normalize_line(self, line: str) -> str:
        return self._spaces.sub(" ", line).strip()

    def _merge_broken_lines(self, lines: list[str]) -> list[str]:
        merged: list[str] = []

        for line in lines:
            if not line:
                if merged and merged[-1] != "":
                    merged.append("")

                continue

            if merged and self._should_merge(merged[-1], line):
                merged[-1] = f"{merged[-1]} {line}"
                continue

            merged.append(line)

        return merged

    def _should_merge(self, previous: str, current: str) -> bool:
        if not previous or previous.endswith((".", ":", ";", "!", "?")):
            return False

        if self._looks_like_section_heading(previous) or self._looks_like_section_heading(current):
            return False

        if re.search(r"[@/]|https?://|\d{3,}", previous + current):
            return False

        return len(previous) < 80 and current[:1].islower()

    def _looks_like_section_heading(self, line: str) -> bool:
        if len(line) > 40:
            return False

        letters = [char for char in line if char.isalpha()]

        if not letters:
            return False

        uppercase_count = sum(1 for char in letters if char.isupper())

        return uppercase_count / len(letters) > 0.7

