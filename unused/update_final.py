#!/usr/bin/env python3
"""
Update api.php docs to include success, result, and error fields in all response examples
"""

try:
    # Read file with proper encoding detection
    with open('docs/api.php', 'r', encoding='utf-8') as f:
        lines = f.readlines()
    
    updated_lines = []
    i = 0
    replacements_made = 0
    
    while i < len(lines):
        line = lines[i]
        
        # Check if this is a "data": true/false line without success/result/error
        if '"data": true' in line or '"data": false' in line:
            # Look ahead to see if the next line is just the closing brace
            if i + 1 < len(lines) and lines[i + 1].strip() == '}' + ('</code></pre>' if i + 2 < len(lines) and '</code></pre>' in lines[i + 2] else ''):
                # This is a response that needs updating
                data_value = 'true' if '"data": true' in line else 'false'
                success_value = 'true'  # Success is true for 200 responses
                
                # Extract the indentation
                indent = len(line) - len(line.lstrip())
                indent_str = line[:indent]
                
                # Add the updated line with additional fields
                updated_line = f'{indent_str}"{line.strip()},\n'
                updated_lines.append(updated_line)
                updated_lines.append(f'{indent_str}"success": {success_value},\n')
                updated_lines.append(f'{indent_str}"result": {data_value},\n')
                updated_lines.append(f'{indent_str}"error": null\n')
                replacements_made += 1
                i += 1
            else:
                updated_lines.append(line)
                i += 1
        else:
            updated_lines.append(line)
            i += 1
    
    # Write back
    with open('docs/api.php', 'w', encoding='utf-8') as f:
        f.writelines(updated_lines)
    
    with open('update_status.txt', 'w') as status:
        status.write(f"Updates completed. Replacements made: {replacements_made}\n")
        status.write(f"Total lines processed: {len(lines)}\n")
    
except Exception as e:
    with open('update_error.txt', 'w') as error_file:
        error_file.write(f"Error: {str(e)}\n")
        import traceback
        error_file.write(traceback.format_exc())
