# Terminal Joy - DualPane File Manager

A fast, lightweight two-column CLI file manager written in PHP with Mac-friendly keyboard controls, emoji icons, and a warm eye-friendly color scheme.

![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue)
![License](https://img.shields.io/badge/license-MIT-green)
![Platform](https://img.shields.io/badge/platform-macOS%20%7C%20Linux-lightgrey)

## ✨ Features

- **Dual-pane interface** - Two side-by-side panels for easy file operations
- **Emoji file icons** - 📂 folders, 🐘 PHP, 🐍 Python, 📜 JavaScript, and more
- **Warm color scheme** - Easy on the eyes with a dark warm gray background
- **Unicode box drawing** - Beautiful rounded corners (╭╮╰╯) and clean borders
- **Mac-friendly controls** - Vim-style navigation (no Home/End/PageUp/PageDown needed)
- **Built-in file viewer** - View text files with scrolling, search, and line numbers
- **Syntax highlighting** - Color-coded display for PHP, Python, JavaScript, Ruby, Go, Rust, C/C++, Bash, and SQL
- **File operations** - Copy, move, and delete files between panels
- **Search & filter** - Quickly find files by name
- **Go to path** - Navigate directly to any directory
- **Terminal resize support** - Automatically adapts when you resize your terminal
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

## 🎨 File Icons

| Type | Icon |
|------|------|
| Folders | 📂 |
| Parent directory | ⬆️ |
| PHP | 🐘 |
| Python | 🐍 |
| JavaScript | 📜 |
| TypeScript | 📘 |
| React (JSX/TSX) | ⚛️ |
| Ruby | 💎 |
| Go | 🔷 |
| Rust | 🦀 |
| C/C++ | 🔧 |
| Java | ☕ |
| Swift | 🍎 |
| HTML | 🌐 |
| CSS/SCSS | 🎨 |
| JSON | 📋 |
| Markdown | 📖 |
| Images | 🖼️ |
| Videos | 🎬 |
| Audio | 🎵 |
| Archives | 📦 |
| Shell scripts | 🐚 |
| Config files | ⚙️ |
| Lock files | 🔒 |

## 📋 Requirements

- **PHP 7.4+** (CLI version)
- **Unix-like terminal** (macOS, Linux, WSL)
- `stty` command (standard on Unix systems)
- Terminal with Unicode/emoji support

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

## 🎨 Syntax Highlighting

The file viewer includes syntax highlighting for the following languages:

| Language | File Extensions |
|----------|-----------------|
| PHP | `.php` |
| Python | `.py` |
| JavaScript/TypeScript | `.js`, `.jsx`, `.ts`, `.tsx` |
| Ruby | `.rb` |
| Go | `.go` |
| Rust | `.rs` |
| C/C++ | `.c`, `.h`, `.cpp`, `.hpp`, `.cc` |
| Bash/Shell | `.sh`, `.bash`, `.zsh` |
| SQL | `.sql`, `.mysql` |

**Color scheme:**
- 🟣 **Keywords** - Purple/bold (function, class, if, return, etc.)
- 🟢 **Strings** - Green ("text", 'text')
- 🔵 **Variables** - Blue ($var, @var)
- 🟡 **Operators** - Yellow (+, -, =, etc.)
- 🟠 **Numbers** - Orange (123, 3.14)
- 🔷 **Function calls** - Cyan (myFunc())
- ⬜ **Comments** - Dim/gray (// comment, # comment)

## 📄 License

MIT License - feel free to use, modify, and distribute.

## 🤝 Contributing

Contributions are welcome! Feel free to:

- Report bugs
- Suggest features
- Submit pull requests

---

Made with ❤️ for terminal enthusiasts
