#!/usr/bin/env python3
"""
Quick test to verify the characters/random-logical fix
"""
import requests
import json

def test_random_logical():
    url = "http://localhost/ananya/api.php/characters/random-logical"
    params = {
        'language': 'telugu',
        'string': 'కండలు',
        'count': '3'
    }
    
    try:
        response = requests.get(url, params=params, timeout=10)
        if response.status_code == 200:
            try:
                data = response.json()
                print(f"✅ SUCCESS: characters/random-logical")
                print(f"   Response: {data['message']}")
                print(f"   Status: {data['response_code']}")
                return True
            except json.JSONDecodeError:
                print(f"❌ FAIL: Invalid JSON response")
                print(f"   Raw: {response.text[:100]}")
                return False
        else:
            print(f"❌ FAIL: HTTP {response.status_code}")
            return False
    except Exception as e:
        print(f"❌ ERROR: {e}")
        return False

if __name__ == "__main__":
    print("Testing characters/random-logical fix...")
    test_random_logical()