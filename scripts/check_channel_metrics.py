#!/usr/bin/env python3
"""
Check channel metrics per router from the WPAMesh Meshview API.

Fetches telemetry data for each active router and calculates:
- Per-router channel utilization and airtime TX
- Network-wide averages

Usage:
    python check_channel_metrics.py
"""

import requests
from datetime import datetime, timedelta, timezone

API_BASE = "https://map.wpamesh.net/api"
DAYS_ACTIVE = 3
ROUTER_ROLES = ["ROUTER", "ROUTER_LATE", "REPEATER"]


def fetch_json(endpoint, params=None):
    """Fetch JSON from the API."""
    url = f"{API_BASE}{endpoint}"
    response = requests.get(url, params=params, timeout=10)
    response.raise_for_status()
    return response.json()


def parse_telemetry_payload(payload):
    """Parse protobuf text format payload for channel metrics."""
    metrics = {}
    if not payload:
        return metrics

    import re

    cu_match = re.search(r'channel_utilization:\s*([\d.]+)', payload)
    if cu_match:
        metrics['channel_utilization'] = float(cu_match.group(1))

    air_match = re.search(r'air_util_tx:\s*([\d.]+)', payload)
    if air_match:
        metrics['air_util_tx'] = float(air_match.group(1))

    return metrics


def get_routers():
    """Get list of active routers."""
    data = fetch_json("/nodes", {"days_active": DAYS_ACTIVE})
    nodes = data.get("nodes", [])

    routers = []
    for node in nodes:
        if node.get("role") in ROUTER_ROLES:
            routers.append({
                "node_id": node["node_id"],
                "long_name": node.get("long_name", "Unknown"),
                "short_name": node.get("short_name", "?"),
                "role": node.get("role", "Unknown"),
            })

    return routers


def get_node_channel_metrics(node_id):
    """Get channel metrics for a specific node."""
    try:
        data = fetch_json("/packets", {
            "port_num": 67,
            "from_node_id": node_id,
            "length": 100,
        })
    except requests.RequestException:
        return None

    packets = data.get("packets", [])
    if not packets:
        return None

    # Calculate cutoff time
    cutoff = datetime.now(timezone.utc) - timedelta(days=DAYS_ACTIVE)
    cutoff_us = int(cutoff.timestamp() * 1_000_000)

    samples = []
    for packet in packets:
        import_time_us = packet.get("import_time_us", 0)
        if import_time_us < cutoff_us:
            continue

        payload = packet.get("payload", "")
        metrics = parse_telemetry_payload(payload)

        if "channel_utilization" in metrics:
            samples.append({
                "channel_utilization": metrics["channel_utilization"],
                "air_util_tx": metrics.get("air_util_tx", 0),
            })

    if not samples:
        return None

    # Average the samples
    avg_cu = sum(s["channel_utilization"] for s in samples) / len(samples)
    avg_air = sum(s["air_util_tx"] for s in samples) / len(samples)

    return {
        "channel_utilization": round(avg_cu, 1),
        "air_util_tx": round(avg_air, 2),
        "sample_count": len(samples),
    }


def main():
    print("Fetching active routers...")
    routers = get_routers()
    print(f"Found {len(routers)} routers\n")

    print("=" * 80)
    print(f"{'Name':<30} {'Role':<12} {'Ch Util %':<10} {'Air TX %':<10} {'Samples':<8}")
    print("=" * 80)

    total_cu = 0
    total_air = 0
    reporting = 0

    for router in routers:
        metrics = get_node_channel_metrics(router["node_id"])

        if metrics:
            cu = metrics["channel_utilization"]
            air = metrics["air_util_tx"]
            samples = metrics["sample_count"]

            total_cu += cu
            total_air += air
            reporting += 1

            print(f"{router['long_name']:<30} {router['role']:<12} {cu:<10.1f} {air:<10.2f} {samples:<8}")
        else:
            print(f"{router['long_name']:<30} {router['role']:<12} {'--':<10} {'--':<10} {'0':<8}")

    print("=" * 80)

    if reporting > 0:
        avg_cu = round(total_cu / reporting, 1)
        avg_air = round(total_air / reporting, 2)
        print(f"\n{'NETWORK AVERAGE':<30} {'':<12} {avg_cu:<10.1f} {avg_air:<10.2f} {reporting} routers")
    else:
        print("\nNo routers reporting channel metrics.")

    print()


if __name__ == "__main__":
    main()
