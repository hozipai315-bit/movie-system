import sys

def count_words(text):
    return len(text.split())

with open('appendix_draft.txt', 'r') as f:
    content = f.read()

# Check Appendix A
a_start = content.find("APPENDIX A")
c_start = content.find("APPENDIX C")
appendix_a = content[a_start:c_start]
print(f"Appendix A word count: {count_words(appendix_a)}")

# Check Appendix C
appendix_c = content[c_start:]
print(f"Appendix C exists: {len(appendix_c) > 100}")

# Check for forbidden words
forbidden = ["leveraging", "harnessing", "robust", "seamless", "cutting-edge", "innovative", "utilizing", "state-of-the-art", "furthermore", "moreover", "employs", "delve", "encompass", "realm", "notable"]
found = []
for word in forbidden:
    if word in content.lower():
        found.append(word)
print(f"Forbidden words found: {found}")
