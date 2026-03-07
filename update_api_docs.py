#!/usr/bin/env python3
import re

# Read the file
with open('docs/api.php', 'r', encoding='utf-8') as f:
    content = f.read()

print(f"Original file size: {len(content)} bytes")

# Pattern to match response blocks that only have 4 fields
# and need success, result, and error added
pattern = r'("response_code": 200,\s*"message": "[^"]+",\s*"string": "[^"]*",\s*"language": "[^"]+",\s*"data": )(true|false)(\s*\})'

def add_response_fields(match):
    prefix = match.group(1)
    data_value = match.group(2)
    suffix = match.group(3)
    
    return f'{prefix}{data_value},\n  "success": true,\n  "result": {data_value},\n  "error": null{suffix}'

new_content = re.sub(pattern, add_response_fields, content, flags=re.MULTILINE)

print(f"Updated file size: {len(new_content)} bytes")

# Now fix specific string values for better examples
replacements = [
    ('All logical characters check completed",\n  "string": "hello",\n  "language": "telugu",',
     'All logical characters check completed",\n  "string": "అనన్య",\n  "language": "telugu",'),
]

for old, new in replacements:
    new_content = new_content.replace(old, new)
    if old in content:
        print(f"Updated: {old[:50]}...")

# Write the file back
with open('docs/api.php', 'w', encoding='utf-8') as f:
    f.write(new_content)

print("File updated successfully!")
