#!/usr/bin/env python3
"""Lightweight API contract check for the router-based API (api.php).

This script validates a focused set of contract expectations:
- Required-parameter behavior for selected endpoints
- HTTP status and message values
- Response shape keys expected by clients

Usage:
  python scripts/contract_check.py
  python scripts/contract_check.py --base-url http://localhost/ananya/api.php
"""

from __future__ import annotations

import argparse
import json
import sys
import urllib.error
import urllib.parse
import urllib.request
from dataclasses import dataclass
from typing import Dict, List, Optional, Set


@dataclass
class ContractCase:
    name: str
    path: str
    params: Dict[str, str]
    expected_status: int
    expected_message: str
    required_keys: Set[str]


@dataclass
class MissingParamCase:
    name: str
    path: str
    params: Dict[str, str]
    remove_param: str
    expected_status: int
    expected_message_contains: str


def request_json(base_url: str, path: str, params: Dict[str, str]) -> tuple[int, Dict[str, object], str]:
    url = f"{base_url.rstrip('/')}/{path.lstrip('/')}"
    if params:
        url += "?" + urllib.parse.urlencode(params)

    req = urllib.request.Request(url, method="GET")
    status: Optional[int] = None
    body: str = ""

    try:
        with urllib.request.urlopen(req, timeout=20) as resp:
            status = resp.getcode()
            body = resp.read().decode("utf-8", errors="replace")
    except urllib.error.HTTPError as exc:
        status = exc.code
        body = exc.read().decode("utf-8", errors="replace")
    except Exception as exc:  # pragma: no cover - runtime environment issue
        raise RuntimeError(f"Request failed for {url}: {exc}") from exc

    try:
        payload = json.loads(body)
    except json.JSONDecodeError as exc:
        raise RuntimeError(f"Non-JSON response for {url} (status {status}): {body}") from exc

    if not isinstance(payload, dict):
        raise RuntimeError(f"Unexpected JSON type for {url}: {type(payload).__name__}")

    return status, payload, url


def run_contract_cases(base_url: str, cases: List[ContractCase]) -> List[str]:
    failures: List[str] = []
    for case in cases:
        try:
            status, payload, url = request_json(base_url, case.path, case.params)
        except RuntimeError as exc:
            failures.append(f"{case.name}: {exc}")
            continue

        if status != case.expected_status:
            failures.append(
                f"{case.name}: expected status {case.expected_status}, got {status} ({url})"
            )

        actual_message = str(payload.get("message", ""))
        if actual_message != case.expected_message:
            failures.append(
                f"{case.name}: expected message '{case.expected_message}', got '{actual_message}'"
            )

        missing = sorted(k for k in case.required_keys if k not in payload)
        if missing:
            failures.append(f"{case.name}: missing required response keys: {', '.join(missing)}")

    return failures


def run_missing_param_cases(base_url: str, cases: List[MissingParamCase]) -> List[str]:
    failures: List[str] = []
    for case in cases:
        test_params = dict(case.params)
        test_params.pop(case.remove_param, None)

        try:
            status, payload, _ = request_json(base_url, case.path, test_params)
        except RuntimeError as exc:
            failures.append(f"{case.name}: {exc}")
            continue

        if status != case.expected_status:
            failures.append(
                f"{case.name}: expected status {case.expected_status} when '{case.remove_param}' is missing, got {status}"
            )

        actual_message = str(payload.get("message", ""))
        if case.expected_message_contains not in actual_message:
            failures.append(
                f"{case.name}: expected message containing '{case.expected_message_contains}', got '{actual_message}'"
            )

    return failures


def main() -> int:
    parser = argparse.ArgumentParser(description="Run lightweight API contract checks")
    parser.add_argument(
        "--base-url",
        default="http://localhost/ananya/api.php",
        help="Base router URL (default: http://localhost/ananya/api.php)",
    )
    args = parser.parse_args()

    contract_cases: List[ContractCase] = [
        ContractCase(
            name="auth_login_test_mode_success",
            path="auth/login",
            params={"email": "test@example.com", "password": "password123"},
            expected_status=200,
            expected_message="Login successful",
            required_keys={"response_code", "message", "data", "success", "error"},
        ),
        ContractCase(
            name="auth_user_exists_test_mode",
            path="auth/user-exists",
            params={"email": "test@example.com"},
            expected_status=200,
            expected_message="User exists",
            required_keys={"response_code", "message", "data", "success", "error"},
        ),
        ContractCase(
            name="characters_add_at_success",
            path="characters/add-at",
            params={"string": "hello", "input2": "2", "input3": "X", "language": "english"},
            expected_status=200,
            expected_message="Character added at position",
            required_keys={"response_code", "message", "string", "language", "data", "success", "result", "error"},
        ),
        ContractCase(
            name="characters_random_logical_without_string",
            path="characters/random-logical",
            params={"count": "5", "language": "telugu"},
            expected_status=200,
            expected_message="Random logical characters generated",
            required_keys={"response_code", "message", "string", "language", "data", "success", "result", "error"},
        ),
        ContractCase(
            name="characters_filler_success",
            path="characters/filler",
            params={"count": "3", "type": "consonant", "language": "english"},
            expected_status=200,
            expected_message="Filler characters generated",
            required_keys={"response_code", "message", "string", "language", "data", "success", "result", "error"},
        ),
    ]

    missing_param_cases: List[MissingParamCase] = [
        MissingParamCase(
            name="auth_login_missing_email",
            path="auth/login",
            params={"email": "test@example.com", "password": "password123"},
            remove_param="email",
            expected_status=400,
            expected_message_contains="Missing required parameter: email",
        ),
        MissingParamCase(
            name="auth_login_missing_password",
            path="auth/login",
            params={"email": "test@example.com", "password": "password123"},
            remove_param="password",
            expected_status=400,
            expected_message_contains="Missing required parameter: password",
        ),
        MissingParamCase(
            name="auth_user_exists_missing_email",
            path="auth/user-exists",
            params={"email": "test@example.com"},
            remove_param="email",
            expected_status=400,
            expected_message_contains="Missing required parameter: email",
        ),
        MissingParamCase(
            name="characters_random_logical_missing_language",
            path="characters/random-logical",
            params={"count": "5", "language": "telugu"},
            remove_param="language",
            expected_status=400,
            expected_message_contains="Missing required parameter: language",
        ),
        MissingParamCase(
            name="characters_filler_missing_language",
            path="characters/filler",
            params={"count": "3", "type": "consonant", "language": "english"},
            remove_param="language",
            expected_status=400,
            expected_message_contains="Missing required parameter: language",
        ),
    ]

    failures: List[str] = []
    failures.extend(run_contract_cases(args.base_url, contract_cases))
    failures.extend(run_missing_param_cases(args.base_url, missing_param_cases))

    if failures:
        print("CONTRACT CHECK FAILED")
        for item in failures:
            print(f"- {item}")
        return 1

    print("CONTRACT CHECK PASSED")
    print(f"Base URL: {args.base_url}")
    print(f"Checks run: {len(contract_cases) + len(missing_param_cases)}")
    return 0


if __name__ == "__main__":
    sys.exit(main())
