# Extract note positions and lanes from a Psych Engine JSON chart and write them into a simple `chart.json` array for this PHP project.
# NOTE: This file was generated with AI.

import json

input_path = r"C:\Users\NoomS\OneDrive\Documents\Games\[ Friday Night Funkin ]\PsychEngine\mods\Nooms Funni Shit\data\pumpkin-pie\pumpkin-pie-rechart.json"
output_path = "chart.json"

def main():
    with open(input_path, "r", encoding="utf-8") as f:
        data = json.load(f)

    notes_out = []
    for section in data.get("notes", []):
        for n in section.get("sectionNotes", []):
            if len(n) >= 2:
                pos = n[0]
                lane = n[1]
                notes_out.append({"position": pos, "lane": lane})

    with open(output_path, "w", encoding="utf-8") as f:
        json.dump(notes_out, f, indent=4)

    print(f"Wrote {len(notes_out)} notes to {output_path}")

if __name__ == "__main__":
    main()
