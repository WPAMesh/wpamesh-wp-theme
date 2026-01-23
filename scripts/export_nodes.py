#!/usr/bin/env python3
"""
Export WPAmesh node data to CSV.

Fetches core_router and supplemental nodes from the WPAmesh REST API
and outputs a CSV with node details.
"""

import csv
import sys
import subprocess
import json
import tempfile
import os

API_URL = "https://wpamesh.net/wp-json/wpamesh/v1/nodes"

def fetch_all_nodes():
    """Fetch all nodes from the API using curl (via temp file to avoid WAF)."""
    with tempfile.NamedTemporaryFile(mode='w', suffix='.json', delete=False) as f:
        tmp_path = f.name

    try:
        result = subprocess.run(
            ["curl", "-s", "-o", tmp_path, API_URL],
            timeout=30
        )
        if result.returncode != 0:
            print(f"Error: curl failed with code {result.returncode}", file=sys.stderr)
            return []

        with open(tmp_path, 'r') as f:
            return json.load(f)
    except subprocess.TimeoutExpired:
        print("Error: Request timed out", file=sys.stderr)
        return []
    except json.JSONDecodeError as e:
        print(f"Error parsing JSON: {e}", file=sys.stderr)
        return []
    finally:
        if os.path.exists(tmp_path):
            os.unlink(tmp_path)

def main():
    # Fetch all nodes
    all_nodes = fetch_all_nodes()

    # Filter to core_router and supplemental tiers locally
    filtered_nodes = [
        n for n in all_nodes
        if n.get("node_tier") in ("core_router", "supplemental")
    ]

    # Write CSV to stdout
    writer = csv.writer(sys.stdout)
    writer.writerow([
        "NodeID",
        "LongName",
        "ShortName",
        "Lat",
        "Lon",
        "AntennaDB",
        "HeightAGL",
        "HeightMSL"
    ])

    for node in filtered_nodes:
        pos = node.get("position") or {}
        ant = node.get("antenna") or {}
        writer.writerow([
            node.get("node_id", ""),
            node.get("long_name", ""),
            node.get("short_name", ""),
            pos.get("latitude", ""),
            pos.get("longitude", ""),
            ant.get("gain_dbi", ""),
            ant.get("agl_m", ""),
            ant.get("msl_m", "")
        ])

if __name__ == "__main__":
    main()
