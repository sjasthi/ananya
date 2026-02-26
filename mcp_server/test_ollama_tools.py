"""Quick test: does Ollama's Mistral handle tool-calling via /v1/chat/completions?"""
import time
import openai

client = openai.OpenAI(api_key="ollama", base_url="http://localhost:11434/v1")

# Step 1: warm up model (no tools)
print("1) Warming up model (no tools)...")
t0 = time.time()
r = client.chat.completions.create(
    model="mistral",
    messages=[{"role": "user", "content": "Say hello in one word."}],
    max_tokens=10,
)
print(f"   Done in {time.time()-t0:.1f}s → {r.choices[0].message.content}")

# Step 2: test with 1 tool
print("\n2) Testing with 1 tool definition...")
tools_1 = [{
    "type": "function",
    "function": {
        "name": "check_palindrome",
        "description": "Check if a word is a palindrome",
        "parameters": {
            "type": "object",
            "properties": {"word": {"type": "string"}},
            "required": ["word"]
        }
    }
}]
t0 = time.time()
try:
    r = client.chat.completions.create(
        model="mistral",
        messages=[{"role": "user", "content": "Is racecar a palindrome?"}],
        tools=tools_1,
        max_tokens=200,
        temperature=0.2,
    )
    print(f"   Done in {time.time()-t0:.1f}s")
    print(f"   finish_reason: {r.choices[0].finish_reason}")
    print(f"   content: {r.choices[0].message.content}")
    print(f"   tool_calls: {r.choices[0].message.tool_calls}")
except Exception as e:
    print(f"   FAILED in {time.time()-t0:.1f}s: {e}")

print("\nDone.")
