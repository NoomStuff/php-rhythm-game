# Goofy ahh PHP rhythm game

Small PHP terminal rhythm game. 
This is mostly a fun project, not a serious engine or anything.

## How 2 run it

You need PHP installed and available in your terminal.

Open the project folder in your terminal and run:
```powershell
php main.php
```

### Where it does and does not run

This project uses `stty` and raw terminal mode stuff for input + screen drawing. Input/rendering will be weird or just fail if your terminal doesn't have it.

It works on terminals that support Unix-style `stty` behavior like Linux & Mac.
It usually shits the bed in plain Windows PowerShell/cmd so you might have to install WSL or something.

## Settings explained

There's a `settings.json` file where you can change some settings:
- `keybinds`: keys that have to be pressed to hit the notes
- `scrollSpeed`: amount of time in ms between each row of characters
- `countdown`: time in ms before the first note starts
- `strumLinePosition`: position of the play field as a percentage of the window width (`0.5` = middle)

## Charting

`chart.json` is where the notes are loaded from.
Each note is basically:
```json
{ "position": 1234, "lane": 0 }
```

`position` is in ms, `lane` is lane index. You can actually have as many lanes as you want, but the game will only render the amount of lanes as there are keybinds.
There's a script in `scripts/FNF-to-PHP.py` that can convert Friday Night Funkin' JSON charts into this format, just put the chart you want to convert in `scripts/input_chart.json` and run the script. You can also just make your own `chart.json` if you want to but uhm have fun doing that by hand lmao.

## Other shi

This was originally a school project to make a PHP terminal game, but I got a little carried away as always.

Code is whatever do you want with it, I don't care.
Contributions are open if you feel like it, I'd love to see them, but know this project is not going anywhere and is mostly just for fun.