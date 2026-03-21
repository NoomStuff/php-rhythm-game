# Extract note positions and lanes from a Friday Night Funkin' chart and write them into a simple `chart.json`

from os import path
import json

SCRIPTS_DIR = path.dirname(__file__)
BASE_DIR = path.dirname(SCRIPTS_DIR)

input_path = path.join(SCRIPTS_DIR, "input_chart.json")
output_path = path.join(BASE_DIR, "chart.json")

def main():
    if not path.exists(input_path):
        print(f"Input file not found, please change the path or add one here: {input_path}")
        return

    try:
        with open(input_path, "r", encoding="utf-8") as f:
            data = json.load(f)

        notes_output = []
        for section in data.get("notes", []):
            for note in section.get("sectionNotes", []):
                if len(note) >= 2:
                    pos = note[0]
                    lane = note[1]
                    notes_output.append({"position": pos, "lane": lane})

        with open(output_path, "w", encoding="utf-8") as f:
            json.dump(notes_output, f, indent=4)

        print(f"Converted {len(notes_output)} notes from the FNF chart to {output_path}")
    except Exception as e:
        print(f"Something went wrong converting the file, make sure the {input_path} is a valid FNF chart: {e}")

if __name__ == "__main__":
    main()
