import json

# Read the file
with open('docs/api.php', 'rb') as f:
    content = f.read().decode('utf-8', errors='replace')

#Count responses before
count_before = content.count('"response_code": 200')

# Simple string-based replacements for validation responses
# We'll add the missing fields to each response JSON block

# Match pattern: closing brace after "data": true/false
replacements = [
    ('  "data": true\n}', '  "data": true,\n  "success": true,\n  "result": true,\n  "error": null\n}'),
    ('  "data": false\n}', '  "data": false,\n  "success": true,\n  "result": false,\n  "error": null\n}'),
]

for old, new in replacements:
    count_old = content.count(old)
    content = content.replace(old, new)
    print(f"Replaced {count_old} instances of response ending")

# Fix the contains-all-logical-chars string to use proper Telugu
content = content.replace(
    '"message": "All logical characters check completed",\n  "string": "à°…à°®à±†à°°à°¿à°•à°¾à°†à°¸à±à°Ÿà±à°°à±‡à°²à°¿à°¯à°¾",',
    '"message": "All logical characters check completed",\n  "string": "అనన్య",'
)

print(f"Total 200 responses: {count_before}")
print(f"New total 200 responses: {content.count(\"response_code\": 200)}")

# Write the file
with open('docs/api.php', 'wb') as f:
    f.write(content.encode('utf-8'))

print("File updated!")
