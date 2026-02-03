# DualPane File Manager

A fast, lightweight two-column CLI file manager written in PHP with Mac-friendly keyboard controls.

![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue)
![License](https://img.shields.io/badge/license-MIT-green)
![Platform](https://img.shields.io/badge/platform-macOS%20%7C%20Linux-lightgrey)

## ✨ Features

- **Dual-pane interface** - Two side-by-side panels for easy file operations
- **Mac-friendly controls** - Vim-style navigation (no Home/End/PageUp/PageDown needed)
- **Built-in file viewer** - View text files with scrolling, search, and line numbers
- **File operations** - Copy, move, and delete files between panels
- **Search & filter** - Quickly find files by name
- **Go to path** - Navigate directly to any directory
- **No dependencies** - Just PHP and a terminal

## 🚀 Quick Start

Run directly from GitHub with a single command:

```bash
php <(curl -sL https://raw.githubusercontent.com/ATsahikian/terminal-joy/main/filemanager.php)
```

Or using wget:

```bash
curl -sL https://raw.githubusercontent.com/ATsahikian/terminal-joy/main/filemanager.php | php
```

## 📦 Installation

### Option 1: Create a shell alias (Recommended)

Add to your `~/.zshrc` or `~/.bashrc`:

```bash
alias fm='php <(curl -sL https://raw.githubusercontent.com/ATsahikian/terminal-joy/main/filemanager.php)'
```

Then reload your shell:

```bash
source ~/.zshrc  # or source ~/.bashrc
```

Now just type `fm` to launch!

### Option 2: Download locally

```bash
# Download
curl -o ~/bin/fm.php https://raw.githubusercontent.com/ATsahikian/terminal-joy/main/filemanager.php

# Make executable
chmod +x ~/bin/fm.php

# Run
php ~/bin/fm.php
```

### Option 3: Clone the repository

```bash
git clone https://github.com/ATsahikian/terminal-joy.git
cd terminal-joy
php filemanager.php
```

## ⌨️ Keyboard Controls

### File Manager

| Action | Keys |
|--------|------|
| Navigate up/down | `↑`/`↓` or `j`/`k` |
| Switch panels | `←`/`→` or `h`/`l` or `Tab` |
| Page up/down | `u` / `d` |
| Jump to first file | `0` (zero) |
| Jump to last file | `$` (Shift+4) |
| Enter directory / View file | `Enter` |
| Go to parent directory | `Backspace` or `-` |
| Go to path | `p` |
| Search/filter files | `/` |
| Next search result | `n` |
| Clear search | `Esc` |
| Copy file to other panel | `c` |
| Move file to other panel | `m` |
| Delete file/directory | `x` |
| Refresh panels | `r` |
| Quit | `q` |

### Text File Viewer

| Action | Keys |
|--------|------|
| Scroll up/down | `↑`/`↓` or `j`/`k` |
| Page up/down | `u` / `d` |
| Go to beginning | `g` |
| Go to end | `G` (Shift+g) |
| Go to line number | `:` (colon) |
| Search text | `/` |
| Next match | `n` |
| Previous match | `N` (Shift+n) |
| Close viewer | `q` or `Esc` |

## 📋 Requirements

- **PHP 7.4+** (CLI version)
- **Unix-like terminal** (macOS, Linux, WSL)
- `stty` command (standard on Unix systems)

Check if PHP is installed:

```bash
php -v
```

On macOS, PHP comes pre-installed. On Linux:

```bash
# Ubuntu/Debian
sudo apt install php-cli

# Fedora
sudo dnf install php-cli

# Arch
sudo pacman -S php
```

## 🎨 Interface Preview

```
+--[ /home/user/projects ]-------------+ +--[ /home/user/documents ]-----------+
| Name                    Size Modified| | Name                    Size Modified|
+--------------------------------------+ +--------------------------------------+
|/..                      <DIR>        | |/..                      <DIR>        |
|/src                     <DIR>        | |/notes                   <DIR>        |
|/tests                   <DIR>        | | report.pdf            2.4M Jan 15    |
| README.md             4.2K Jan 20    | | todo.txt              1.2K Jan 18    |
| package.json          1.1K Jan 19    | |                                      |
+--------------------------------------+ +--------------------------------------+
Welcome to DualPane File Manager! Press 'q' to quit.
 Arrows/jk:Nav  Tab/hl:Panel  u/d:Page  0/$:Jump  p:Path  /:Find  q:Quit
```

## 📄 License

MIT License - feel free to use, modify, and distribute.

## 🤝 Contributing

Contributions are welcome! Feel free to:

- Report bugs
- Suggest features
- Submit pull requests

---

Made with ❤️ for terminal enthusiasts
