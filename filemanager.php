#!/usr/bin/env php
<?php
/**
 * DualPane - A two-column CLI file manager in PHP
 *
 * File Manager Controls:
 *   Up/Down or j/k - Navigate files
 *   Left/Right or h/l - Switch between panels
 *   Tab            - Switch between panels
 *   u/d            - Page up/down
 *   0 (zero)       - Jump to first file
 *   $ (Shift+4)    - Jump to last file
 *   Enter          - Enter directory / View file
 *   Backspace or - - Go to parent directory
 *   p              - Go to path (type path directly)
 *   /              - Search/filter files by name
 *   n              - Next search result
 *   Esc            - Clear search filter
 *   c              - Copy file to other panel
 *   m              - Move file to other panel
 *   x              - Delete file/directory
 *   r              - Refresh both panels
 *   q              - Quit
 *
 * Text File Viewer Controls:
 *   Up/Down or j/k - Scroll line by line
 *   u/d            - Scroll by page
 *   g              - Go to beginning of file
 *   G (Shift+g)    - Go to end of file
 *   : (colon)      - Go to specific line number
 *   /              - Search text in file
 *   n              - Next search match
 *   N              - Previous search match
 *   q/Esc          - Close file viewer
 */

class DualPaneFileManager
{
    private $termWidth;
    private $termHeight;
    private $panelWidth;
    private $contentHeight;

    private $activePanel = 0; // 0 = left, 1 = right
    private $panels = array();

    private $running = true;
    private $statusMessage = '';
    private $searchFilter = '';
    private $filteredFiles = array();

    // ANSI color codes
    const COLOR_RESET = "\033[0m";
    const COLOR_BOLD = "\033[1m";
    const COLOR_DIM = "\033[2m";
    const COLOR_BLUE = "\033[34m";
    const COLOR_CYAN = "\033[36m";
    const COLOR_GREEN = "\033[32m";
    const COLOR_YELLOW = "\033[33m";
    const COLOR_RED = "\033[31m";
    const COLOR_WHITE = "\033[37m";
    const COLOR_BG_BLUE = "\033[44m";
    const COLOR_BG_GRAY = "\033[100m";
    const COLOR_BG_YELLOW = "\033[43m";
    const COLOR_BLACK = "\033[30m";
    const COLOR_INVERSE = "\033[7m";
    const COLOR_MAGENTA = "\033[35m";
    const COLOR_BRIGHT_BLUE = "\033[94m";
    const COLOR_BRIGHT_GREEN = "\033[92m";
    const COLOR_BRIGHT_YELLOW = "\033[93m";
    const COLOR_BRIGHT_CYAN = "\033[96m";
    const COLOR_BRIGHT_MAGENTA = "\033[95m";
    const COLOR_ORANGE = "\033[38;5;208m";

    // Warm color scheme for eye comfort
    const COLOR_BG_WARM = "\033[48;5;236m";       // Dark warm gray background
    const COLOR_BG_WARM_HEADER = "\033[48;5;95m"; // Warm brown/mauve for headers
    const COLOR_BG_WARM_STATUS = "\033[48;5;60m"; // Muted purple for status bar
    const COLOR_BG_WARM_ACCENT = "\033[48;5;58m"; // Olive/warm accent
    const COLOR_FG_CREAM = "\033[38;5;230m";      // Cream/warm white text

    // Syntax highlighting patterns by language
    private static $syntaxRules = array(
        'php' => array(
            'keywords' => array('function', 'class', 'public', 'private', 'protected', 'static', 'const', 'new', 'return', 'if', 'else', 'elseif', 'while', 'for', 'foreach', 'switch', 'case', 'break', 'continue', 'try', 'catch', 'throw', 'finally', 'use', 'namespace', 'extends', 'implements', 'interface', 'trait', 'abstract', 'final', 'echo', 'print', 'require', 'include', 'require_once', 'include_once', 'array', 'true', 'false', 'null', 'self', 'parent', 'this'),
            'comment' => array('//', '#', '/*', '*/'),
            'string' => array('"', "'"),
        ),
        'python' => array(
            'keywords' => array('def', 'class', 'import', 'from', 'as', 'return', 'if', 'elif', 'else', 'while', 'for', 'in', 'try', 'except', 'finally', 'raise', 'with', 'lambda', 'yield', 'global', 'nonlocal', 'pass', 'break', 'continue', 'True', 'False', 'None', 'and', 'or', 'not', 'is', 'async', 'await', 'self'),
            'comment' => array('#'),
            'string' => array('"', "'", '"""', "'''"),
        ),
        'javascript' => array(
            'keywords' => array('function', 'const', 'let', 'var', 'class', 'extends', 'new', 'return', 'if', 'else', 'while', 'for', 'switch', 'case', 'break', 'continue', 'try', 'catch', 'throw', 'finally', 'async', 'await', 'import', 'export', 'default', 'from', 'true', 'false', 'null', 'undefined', 'this', 'super', 'typeof', 'instanceof', 'of', 'in'),
            'comment' => array('//', '/*', '*/'),
            'string' => array('"', "'", '`'),
        ),
        'ruby' => array(
            'keywords' => array('def', 'class', 'module', 'end', 'if', 'elsif', 'else', 'unless', 'while', 'until', 'for', 'do', 'begin', 'rescue', 'ensure', 'raise', 'return', 'yield', 'require', 'include', 'extend', 'attr_accessor', 'attr_reader', 'attr_writer', 'true', 'false', 'nil', 'self', 'super', 'and', 'or', 'not', 'lambda', 'proc'),
            'comment' => array('#'),
            'string' => array('"', "'"),
        ),
        'go' => array(
            'keywords' => array('func', 'package', 'import', 'type', 'struct', 'interface', 'const', 'var', 'return', 'if', 'else', 'for', 'range', 'switch', 'case', 'default', 'break', 'continue', 'go', 'defer', 'select', 'chan', 'map', 'make', 'new', 'true', 'false', 'nil', 'iota'),
            'comment' => array('//', '/*', '*/'),
            'string' => array('"', "'", '`'),
        ),
        'rust' => array(
            'keywords' => array('fn', 'let', 'mut', 'const', 'struct', 'enum', 'impl', 'trait', 'pub', 'mod', 'use', 'return', 'if', 'else', 'match', 'while', 'for', 'loop', 'break', 'continue', 'async', 'await', 'move', 'ref', 'self', 'Self', 'super', 'true', 'false', 'Some', 'None', 'Ok', 'Err', 'where', 'unsafe', 'extern', 'crate'),
            'comment' => array('//', '/*', '*/'),
            'string' => array('"'),
        ),
        'c' => array(
            'keywords' => array('int', 'char', 'float', 'double', 'void', 'long', 'short', 'unsigned', 'signed', 'const', 'static', 'extern', 'struct', 'union', 'enum', 'typedef', 'sizeof', 'return', 'if', 'else', 'while', 'for', 'do', 'switch', 'case', 'default', 'break', 'continue', 'goto', 'include', 'define', 'ifdef', 'ifndef', 'endif', 'NULL', 'true', 'false'),
            'comment' => array('//', '/*', '*/'),
            'string' => array('"', "'"),
        ),
        'bash' => array(
            'keywords' => array('if', 'then', 'else', 'elif', 'fi', 'for', 'while', 'do', 'done', 'case', 'esac', 'function', 'return', 'exit', 'echo', 'read', 'local', 'export', 'source', 'alias', 'unalias', 'set', 'unset', 'shift', 'true', 'false', 'in'),
            'comment' => array('#'),
            'string' => array('"', "'"),
        ),
        'sql' => array(
            'keywords' => array('SELECT', 'FROM', 'WHERE', 'INSERT', 'UPDATE', 'DELETE', 'CREATE', 'DROP', 'ALTER', 'TABLE', 'INDEX', 'VIEW', 'INTO', 'VALUES', 'SET', 'JOIN', 'LEFT', 'RIGHT', 'INNER', 'OUTER', 'ON', 'AND', 'OR', 'NOT', 'NULL', 'IS', 'IN', 'LIKE', 'ORDER', 'BY', 'GROUP', 'HAVING', 'LIMIT', 'OFFSET', 'AS', 'DISTINCT', 'COUNT', 'SUM', 'AVG', 'MAX', 'MIN', 'TRUE', 'FALSE'),
            'comment' => array('--', '/*', '*/'),
            'string' => array("'"),
        ),
    );

    // File extension to language mapping
    private static $extensionMap = array(
        'php' => 'php',
        'py' => 'python',
        'js' => 'javascript',
        'jsx' => 'javascript',
        'ts' => 'javascript',
        'tsx' => 'javascript',
        'rb' => 'ruby',
        'go' => 'go',
        'rs' => 'rust',
        'c' => 'c',
        'h' => 'c',
        'cpp' => 'c',
        'hpp' => 'c',
        'cc' => 'c',
        'sh' => 'bash',
        'bash' => 'bash',
        'zsh' => 'bash',
        'sql' => 'sql',
        'mysql' => 'sql',
    );

    public function __construct()
    {
        $this->getTerminalSize();
        $this->panelWidth = (int)(($this->termWidth - 3) / 2);
        $this->contentHeight = $this->termHeight - 6;

        $this->panels = array(
            array(
                'path' => getcwd(),
                'files' => array(),
                'selected' => 0,
                'scroll' => 0,
            ),
            array(
                'path' => getcwd(),
                'files' => array(),
                'selected' => 0,
                'scroll' => 0,
            ),
        );

        $this->loadDirectory(0);
        $this->loadDirectory(1);
    }

    private function getTerminalSize()
    {
        $this->termWidth = (int)exec('tput cols');
        if (!$this->termWidth) $this->termWidth = 80;
        $this->termHeight = (int)exec('tput lines');
        if (!$this->termHeight) $this->termHeight = 24;
    }

    private function loadDirectory($panel)
    {
        $path = $this->panels[$panel]['path'];
        $files = array();

        if ($path !== '/') {
            $files[] = array('name' => '..', 'type' => 'dir', 'size' => 0, 'mtime' => 0);
        }

        $items = @scandir($path);
        if ($items === false) {
            $this->statusMessage = "Cannot read directory: $path";
            return;
        }

        $dirs = array();
        $regularFiles = array();

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            $fullPath = $path . '/' . $item;
            $isDir = is_dir($fullPath);
            $size = $isDir ? 0 : (@filesize($fullPath) ?: 0);
            $mtime = @filemtime($fullPath) ?: 0;

            $entry = array(
                'name' => $item,
                'type' => $isDir ? 'dir' : 'file',
                'size' => $size,
                'mtime' => $mtime,
            );

            if ($isDir) {
                $dirs[] = $entry;
            } else {
                $regularFiles[] = $entry;
            }
        }

        usort($dirs, function($a, $b) { return strcasecmp($a['name'], $b['name']); });
        usort($regularFiles, function($a, $b) { return strcasecmp($a['name'], $b['name']); });

        $this->panels[$panel]['files'] = array_merge($files, $dirs, $regularFiles);

        if ($this->panels[$panel]['selected'] >= count($this->panels[$panel]['files'])) {
            $this->panels[$panel]['selected'] = max(0, count($this->panels[$panel]['files']) - 1);
        }
    }

    private function clearScreen()
    {
        // Move cursor to home position instead of clearing
        // This reduces flicker by overwriting content in place
        echo "\033[H";
    }

    private function fullClearScreen()
    {
        // Full clear - only use when necessary
        echo "\033[2J\033[H";
    }

    private function moveCursor($row, $col)
    {
        echo "\033[{$row};{$col}H";
    }

    private function drawBox($x, $y, $width, $height, $title = '')
    {
        // Unicode box-drawing characters
        $horizontal = '─';
        $vertical = '│';
        $topLeft = '╭';
        $topRight = '╮';
        $bottomLeft = '╰';
        $bottomRight = '╯';

        // Top border
        $this->moveCursor($y, $x);
        echo $topLeft . str_repeat($horizontal, $width - 2) . $topRight;

        // Title
        if ($title) {
            $this->moveCursor($y, $x + 2);
            echo "─ " . self::COLOR_BOLD . $title . self::COLOR_RESET . " ─";
        }

        // Sides
        for ($i = 1; $i < $height - 1; $i++) {
            $this->moveCursor($y + $i, $x);
            echo $vertical;
            $this->moveCursor($y + $i, $x + $width - 1);
            echo $vertical;
        }

        // Bottom border
        $this->moveCursor($y + $height - 1, $x);
        echo $bottomLeft . str_repeat($horizontal, $width - 2) . $bottomRight;
    }

    private function getDisplayFilesForPanel($panel)
    {
        if (empty($this->searchFilter) || $panel !== $this->activePanel) {
            return $this->panels[$panel]['files'];
        }

        $filtered = array();
        foreach ($this->panels[$panel]['files'] as $file) {
            if ($file['name'] === '..' || stripos($file['name'], $this->searchFilter) !== false) {
                $filtered[] = $file;
            }
        }
        return $filtered;
    }

    private function truncate($text, $maxLen)
    {
        if (strlen($text) <= $maxLen) {
            return $text;
        }
        return substr($text, 0, $maxLen - 3) . '...';
    }

    private function formatSize($size)
    {
        if ($size < 1024) return sprintf('%7d', $size);
        if ($size < 1024 * 1024) return sprintf('%6.1fK', $size / 1024);
        if ($size < 1024 * 1024 * 1024) return sprintf('%6.1fM', $size / (1024 * 1024));
        return sprintf('%6.1fG', $size / (1024 * 1024 * 1024));
    }

    private function mbDisplayWidth($str)
    {
        // Calculate display width accounting for wide characters (emojis, CJK, etc.)
        $width = 0;
        $len = mb_strlen($str, 'UTF-8');
        for ($i = 0; $i < $len; $i++) {
            $char = mb_substr($str, $i, 1, 'UTF-8');
            $ord = mb_ord($char, 'UTF-8');
            // Skip variation selectors and zero-width joiners (they don't take space)
            if ($ord == 0xFE0F || $ord == 0xFE0E || $ord == 0x200D) {
                continue;
            }
            // Emojis and wide characters take 2 columns
            if ($ord > 0x1F600 || ($ord >= 0x2600 && $ord <= 0x27BF) || $ord > 0x10000) {
                $width += 2;
            } else {
                $width += 1;
            }
        }
        return $width;
    }

    private function padRight($str, $width)
    {
        $displayWidth = $this->mbDisplayWidth($str);
        $padding = max(0, $width - $displayWidth);
        return $str . str_repeat(' ', $padding);
    }

    private function getIconPadded($icon)
    {
        // Ensure icon takes exactly 2 display columns
        $displayWidth = $this->mbDisplayWidth($icon);
        if ($displayWidth < 2) {
            return $icon . str_repeat(' ', 2 - $displayWidth);
        }
        return $icon;
    }

    private function getFileIcon($filename, $isDir)
    {
        if ($isDir) {
            if ($filename === '..') return '⬆️';
            return '📂';
        }

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $icons = array(
            // Programming
            'php' => '🐘',
            'py' => '🐍',
            'js' => '📜',
            'ts' => '📘',
            'jsx' => '⚛️',
            'tsx' => '⚛️',
            'rb' => '💎',
            'go' => '🔷',
            'rs' => '🦀',
            'c' => '🔧',
            'cpp' => '🔧',
            'h' => '📑',
            'java' => '☕',
            'swift' => '🍎',

            // Web
            'html' => '🌐',
            'css' => '🎨',
            'scss' => '🎨',

            // Data
            'json' => '📋',
            'xml' => '📄',
            'yaml' => '📝',
            'yml' => '📝',
            'sql' => '🗄️',
            'csv' => '📊',

            // Documents
            'md' => '📖',
            'txt' => '📄',
            'pdf' => '📕',
            'doc' => '📘',
            'docx' => '📘',

            // Images
            'jpg' => '🖼️',
            'jpeg' => '🖼️',
            'png' => '🖼️',
            'gif' => '🖼️',
            'svg' => '🎭',

            // Media
            'mp3' => '🎵',
            'wav' => '🎵',
            'mp4' => '🎬',
            'mov' => '🎬',

            // Archives
            'zip' => '📦',
            'tar' => '📦',
            'gz' => '📦',
            'rar' => '📦',

            // Config
            'env' => '⚙️',
            'ini' => '⚙️',
            'conf' => '⚙️',

            // Shell
            'sh' => '🐚',
            'bash' => '🐚',
            'zsh' => '🐚',

            // Git
            'gitignore' => '🙈',

            // Lock files
            'lock' => '🔒',
        );

        return isset($icons[$ext]) ? $icons[$ext] : '📄';
    }

    private function drawPanel($panel)
    {
        $isActive = ($panel === $this->activePanel);
        $x = ($panel === 0) ? 1 : $this->panelWidth + 2;
        $y = 1;

        $path = $this->panels[$panel]['path'];
        $displayPath = $this->truncate($path, $this->panelWidth - 6);

        if ($isActive) {
            echo self::COLOR_CYAN;
        }
        $this->drawBox($x, $y, $this->panelWidth, $this->contentHeight + 2, $displayPath);
        echo self::COLOR_RESET . self::COLOR_BG_WARM . self::COLOR_FG_CREAM;

        // Header - account for emoji width (2 chars) + space (1 char) = 3 chars for icon column
        $this->moveCursor($y + 1, $x + 1);
        echo self::COLOR_BG_WARM . self::COLOR_FG_CREAM . self::COLOR_DIM;
        $nameWidth = $this->panelWidth - 27;
        echo sprintf("   %-{$nameWidth}s %7s  %-12s", 'Name', 'Size', 'Modified');
        echo self::COLOR_RESET . self::COLOR_BG_WARM . self::COLOR_FG_CREAM;

        // Draw separator with Unicode
        $this->moveCursor($y + 2, $x);
        echo self::COLOR_BG_WARM . self::COLOR_FG_CREAM . '├' . str_repeat('─', $this->panelWidth - 2) . '┤';

        // Files (apply search filter if active)
        $files = $this->getDisplayFilesForPanel($panel);
        $selected = $this->panels[$panel]['selected'];
        $scroll = $this->panels[$panel]['scroll'];
        $visibleCount = $this->contentHeight - 2;

        // Adjust scroll if needed
        if ($selected < $scroll) {
            $this->panels[$panel]['scroll'] = $selected;
            $scroll = $selected;
        } elseif ($selected >= $scroll + $visibleCount) {
            $this->panels[$panel]['scroll'] = $selected - $visibleCount + 1;
            $scroll = $this->panels[$panel]['scroll'];
        }

        for ($i = 0; $i < $visibleCount; $i++) {
            $fileIndex = $scroll + $i;
            $this->moveCursor($y + 3 + $i, $x + 1);

            if ($fileIndex >= count($files)) {
                echo str_repeat(' ', $this->panelWidth - 2);
                continue;
            }

            $file = $files[$fileIndex];
            $isSelected = ($fileIndex === $selected);
            $isDir = ($file['type'] === 'dir');

            // Get file icon (emoji = 2 char width display) and pad to exactly 2 columns
            $icon = $this->getIconPadded($this->getFileIcon($file['name'], $isDir));

            // Calculate available width - must match header
            $nameWidth = $this->panelWidth - 27;
            $name = $this->truncate($file['name'], $nameWidth);

            if ($isDir) {
                $sizeStr = '<DIR>';
            } else {
                $sizeStr = $this->formatSize($file['size']);
            }

            $dateStr = $file['mtime'] ? date('M d H:i', $file['mtime']) : '';

            // Build the content line using display-width-aware padding
            $namePadded = $this->padRight($name, $nameWidth);
            $sizePadded = sprintf("%7s", $sizeStr);
            $datePadded = sprintf("%-12s", $dateStr);
            $content = $namePadded . ' ' . $sizePadded . '  ' . $datePadded;

            // Calculate padding to fill the entire panel width
            $contentDisplayWidth = $this->mbDisplayWidth($content);
            $lineDisplayWidth = 3 + $contentDisplayWidth;
            $padding = max(0, $this->panelWidth - 2 - $lineDisplayWidth);

            // Apply selection highlighting BEFORE content
            if ($isSelected && $isActive) {
                echo self::COLOR_INVERSE . self::COLOR_BOLD;
            } elseif ($isSelected) {
                echo self::COLOR_BG_GRAY . self::COLOR_WHITE;
            } elseif ($isDir) {
                echo self::COLOR_CYAN;
            }

            // Output: icon + space + content + padding (all highlighted if selected)
            echo $icon . ' ' . $content . str_repeat(' ', $padding);

            echo self::COLOR_RESET . self::COLOR_BG_WARM . self::COLOR_FG_CREAM;
        }
    }

    private function drawStatusBar()
    {
        $this->moveCursor($this->termHeight - 2, 1);
        echo self::COLOR_BG_BLUE . self::COLOR_WHITE;
        echo str_pad($this->statusMessage, $this->termWidth);
        echo self::COLOR_RESET;

        $this->moveCursor($this->termHeight - 1, 1);
        echo self::COLOR_DIM;
        echo " Arrows/jk:Nav  Tab/hl:Panel  u/d:Page  0/$:Jump  p:Path  /:Find  c:Copy  m:Move  x:Del  q:Quit";
        echo self::COLOR_RESET;

        // Show search filter if active
        if (!empty($this->searchFilter)) {
            $this->moveCursor($this->termHeight, 1);
            echo self::COLOR_YELLOW . " Filter: " . $this->searchFilter . self::COLOR_RESET;
        }
    }

    public function draw()
    {
        // Use output buffering to reduce flicker
        ob_start();

        // Hide cursor before drawing
        echo "\033[?25l";

        // Move to home position (don't clear - overwrite in place)
        $this->clearScreen();

        $this->drawPanel(0);
        $this->drawPanel(1);
        $this->drawStatusBar();

        // Flush buffered output all at once
        ob_end_flush();
        flush();
    }

    private function getSelectedFile()
    {
        $files = $this->getDisplayFiles();
        $selected = $this->panels[$this->activePanel]['selected'];
        if (empty($files)) return null;
        return isset($files[$selected]) ? $files[$selected] : null;
    }

    private function getSelectedPath()
    {
        $file = $this->getSelectedFile();
        if (!$file) return null;

        $basePath = $this->panels[$this->activePanel]['path'];
        if ($file['name'] === '..') {
            return dirname($basePath);
        }
        return $basePath . '/' . $file['name'];
    }

    private function navigate($direction)
    {
        $files = $this->getDisplayFiles();
        $newPos = $this->panels[$this->activePanel]['selected'] + $direction;

        if ($newPos >= 0 && $newPos < count($files)) {
            $this->panels[$this->activePanel]['selected'] = $newPos;
        }
    }

    private function navigatePage($direction)
    {
        $files = $this->getDisplayFiles();
        $visibleCount = $this->contentHeight - 2;
        $newPos = $this->panels[$this->activePanel]['selected'] + ($direction * $visibleCount);

        if ($newPos < 0) {
            $newPos = 0;
        } elseif ($newPos >= count($files)) {
            $newPos = max(0, count($files) - 1);
        }

        $this->panels[$this->activePanel]['selected'] = $newPos;
    }

    private function navigateToStart()
    {
        $this->panels[$this->activePanel]['selected'] = 0;
        $this->panels[$this->activePanel]['scroll'] = 0;
    }

    private function navigateToEnd()
    {
        $files = $this->getDisplayFiles();
        $this->panels[$this->activePanel]['selected'] = max(0, count($files) - 1);
    }

    private function getDisplayFiles()
    {
        if (empty($this->searchFilter)) {
            return $this->panels[$this->activePanel]['files'];
        }

        $filtered = array();
        foreach ($this->panels[$this->activePanel]['files'] as $file) {
            if ($file['name'] === '..' || stripos($file['name'], $this->searchFilter) !== false) {
                $filtered[] = $file;
            }
        }
        return $filtered;
    }

    private function goToPath()
    {
        echo "\033[?25h"; // Show cursor
        $this->moveCursor($this->termHeight - 2, 1);
        echo self::COLOR_BG_BLUE . self::COLOR_WHITE;
        echo str_pad(" Go to path: ", $this->termWidth);
        echo self::COLOR_RESET;
        $this->moveCursor($this->termHeight - 2, 14);

        // Restore terminal for input
        system('stty sane');

        $path = trim(fgets(STDIN));

        // Set terminal back to raw mode
        system('stty -icanon -echo');
        echo "\033[?25l"; // Hide cursor

        if (empty($path)) {
            $this->statusMessage = "Go to path cancelled";
            return;
        }

        // Expand ~ to home directory
        if (strpos($path, '~') === 0) {
            $home = getenv('HOME');
            if ($home) {
                $path = $home . substr($path, 1);
            }
        }

        // Handle relative paths
        if ($path[0] !== '/') {
            $path = $this->panels[$this->activePanel]['path'] . '/' . $path;
        }

        $realPath = realpath($path);
        if ($realPath && is_dir($realPath)) {
            $this->panels[$this->activePanel]['path'] = $realPath;
            $this->panels[$this->activePanel]['selected'] = 0;
            $this->panels[$this->activePanel]['scroll'] = 0;
            $this->searchFilter = '';
            $this->loadDirectory($this->activePanel);
            $this->statusMessage = "Navigated to: $realPath";
        } elseif ($realPath && is_file($realPath)) {
            // If it's a file, go to its directory and select it
            $dir = dirname($realPath);
            $filename = basename($realPath);
            $this->panels[$this->activePanel]['path'] = $dir;
            $this->panels[$this->activePanel]['selected'] = 0;
            $this->panels[$this->activePanel]['scroll'] = 0;
            $this->searchFilter = '';
            $this->loadDirectory($this->activePanel);

            // Find and select the file
            foreach ($this->panels[$this->activePanel]['files'] as $i => $file) {
                if ($file['name'] === $filename) {
                    $this->panels[$this->activePanel]['selected'] = $i;
                    break;
                }
            }
            $this->statusMessage = "Navigated to: $dir (selected: $filename)";
        } else {
            $this->statusMessage = "Path not found: $path";
        }
    }

    private function searchFiles()
    {
        echo "\033[?25h"; // Show cursor
        $this->moveCursor($this->termHeight - 2, 1);
        echo self::COLOR_BG_BLUE . self::COLOR_WHITE;
        echo str_pad(" Search: ", $this->termWidth);
        echo self::COLOR_RESET;
        $this->moveCursor($this->termHeight - 2, 10);

        // Restore terminal for input
        system('stty sane');

        $search = trim(fgets(STDIN));

        // Set terminal back to raw mode
        system('stty -icanon -echo');
        echo "\033[?25l"; // Hide cursor

        if (empty($search)) {
            $this->searchFilter = '';
            $this->statusMessage = "Search cleared";
            return;
        }

        $this->searchFilter = $search;
        $this->panels[$this->activePanel]['selected'] = 0;
        $this->panels[$this->activePanel]['scroll'] = 0;

        $filtered = $this->getDisplayFiles();
        $count = count($filtered);
        $this->statusMessage = "Filter: '$search' - Found $count matches (Esc to clear)";
    }

    private function nextSearchResult()
    {
        if (empty($this->searchFilter)) {
            $this->statusMessage = "No active search. Press / to search.";
            return;
        }

        // Search through ALL files, not filtered list
        $allFiles = $this->panels[$this->activePanel]['files'];
        $filteredFiles = $this->getDisplayFiles();
        $currentFilteredIndex = $this->panels[$this->activePanel]['selected'];

        // If we're at the last item in filtered list, wrap to first
        if ($currentFilteredIndex >= count($filteredFiles) - 1) {
            $this->panels[$this->activePanel]['selected'] = 0;
            $this->statusMessage = "Wrapped to first match";
        } else {
            // Move to next item in filtered list
            $this->panels[$this->activePanel]['selected'] = $currentFilteredIndex + 1;
            $file = $filteredFiles[$currentFilteredIndex + 1];
            $this->statusMessage = "Match: " . $file['name'];
        }
    }

    private function clearSearch()
    {
        $this->searchFilter = '';
        $this->statusMessage = "Search filter cleared";
    }

    private function enterDirectory()
    {
        $file = $this->getSelectedFile();
        if (!$file) return;

        if ($file['type'] === 'dir') {
            $newPath = $this->getSelectedPath();
            if (is_readable($newPath)) {
                $this->panels[$this->activePanel]['path'] = realpath($newPath);
                $this->panels[$this->activePanel]['selected'] = 0;
                $this->panels[$this->activePanel]['scroll'] = 0;
                $this->loadDirectory($this->activePanel);
                $this->statusMessage = "Entered: " . $this->panels[$this->activePanel]['path'];
            } else {
                $this->statusMessage = "Cannot access directory: $newPath";
            }
        } else {
            $path = $this->getSelectedPath();
            $this->viewFile($path);
        }
    }

    private function getLanguageFromExtension($path)
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return isset(self::$extensionMap[$ext]) ? self::$extensionMap[$ext] : null;
    }

    private function highlightSyntax($line, $language)
    {
        if (!$language || !isset(self::$syntaxRules[$language])) {
            return $line;
        }

        $rules = self::$syntaxRules[$language];
        $result = '';
        $inString = false;
        $stringChar = '';
        $inComment = false;
        $i = 0;
        $len = strlen($line);

        while ($i < $len) {
            $char = $line[$i];
            $remaining = substr($line, $i);

            // Check for single-line comment start
            if (!$inString && !$inComment) {
                foreach ($rules['comment'] as $commentStart) {
                    if ($commentStart === '//' || $commentStart === '#' || $commentStart === '--') {
                        if (strpos($remaining, $commentStart) === 0) {
                            // Rest of line is a comment
                            $result .= self::COLOR_DIM . substr($line, $i) . self::COLOR_RESET;
                            return $result;
                        }
                    }
                }
            }

            // Check for string start/end
            if (!$inComment) {
                foreach ($rules['string'] as $strChar) {
                    if (strpos($remaining, $strChar) === 0) {
                        if ($inString && $stringChar === $strChar) {
                            // End of string
                            $result .= $strChar . self::COLOR_RESET;
                            $inString = false;
                            $stringChar = '';
                            $i += strlen($strChar);
                            continue 2;
                        } elseif (!$inString) {
                            // Start of string
                            $inString = true;
                            $stringChar = $strChar;
                            $result .= self::COLOR_GREEN . $strChar;
                            $i += strlen($strChar);
                            continue 2;
                        }
                    }
                }
            }

            if ($inString) {
                $result .= $char;
                $i++;
                continue;
            }

            // Check for keywords (must be word boundaries)
            $foundKeyword = false;
            if (ctype_alpha($char) || $char === '_' || $char === '$') {
                foreach ($rules['keywords'] as $keyword) {
                    $kwLen = strlen($keyword);
                    $potentialKeyword = substr($line, $i, $kwLen);

                    // Case-insensitive for SQL, case-sensitive for others
                    $matches = ($language === 'sql')
                        ? (strcasecmp($potentialKeyword, $keyword) === 0)
                        : ($potentialKeyword === $keyword);

                    if ($matches) {
                        // Check word boundary after keyword
                        $afterChar = isset($line[$i + $kwLen]) ? $line[$i + $kwLen] : '';
                        if ($afterChar === '' || !ctype_alnum($afterChar) && $afterChar !== '_') {
                            // Check word boundary before keyword
                            $beforeChar = ($i > 0) ? $line[$i - 1] : '';
                            if ($beforeChar === '' || !ctype_alnum($beforeChar) && $beforeChar !== '_' && $beforeChar !== '$') {
                                $result .= self::COLOR_MAGENTA . self::COLOR_BOLD . $potentialKeyword . self::COLOR_RESET;
                                $i += $kwLen;
                                $foundKeyword = true;
                                break;
                            }
                        }
                    }
                }
            }

            if ($foundKeyword) {
                continue;
            }

            // Check for numbers
            if (ctype_digit($char) || ($char === '.' && isset($line[$i + 1]) && ctype_digit($line[$i + 1]))) {
                $numStart = $i;
                while ($i < $len && (ctype_digit($line[$i]) || $line[$i] === '.' || $line[$i] === 'x' || ctype_xdigit($line[$i]))) {
                    $i++;
                }
                $result .= self::COLOR_ORANGE . substr($line, $numStart, $i - $numStart) . self::COLOR_RESET;
                continue;
            }

            // Check for function calls (word followed by parenthesis)
            if (ctype_alpha($char) || $char === '_') {
                $wordStart = $i;
                while ($i < $len && (ctype_alnum($line[$i]) || $line[$i] === '_')) {
                    $i++;
                }
                $word = substr($line, $wordStart, $i - $wordStart);

                // Check if followed by (
                if (isset($line[$i]) && $line[$i] === '(') {
                    $result .= self::COLOR_BRIGHT_CYAN . $word . self::COLOR_RESET;
                } else {
                    $result .= $word;
                }
                continue;
            }

            // Check for special variables ($var in PHP, @var in Ruby)
            if ($char === '$' || ($language === 'ruby' && $char === '@')) {
                $varStart = $i;
                $i++;
                while ($i < $len && (ctype_alnum($line[$i]) || $line[$i] === '_')) {
                    $i++;
                }
                $result .= self::COLOR_BRIGHT_BLUE . substr($line, $varStart, $i - $varStart) . self::COLOR_RESET;
                continue;
            }

            // Operators and brackets
            if (strpos('{}[]()=<>+-*/%&|!^~:;,.', $char) !== false) {
                $result .= self::COLOR_YELLOW . $char . self::COLOR_RESET;
                $i++;
                continue;
            }

            $result .= $char;
            $i++;
        }

        // If we're still in a string at end of line, close the color
        if ($inString) {
            $result .= self::COLOR_RESET;
        }

        return $result;
    }

    private function viewFile($path)
    {
        $content = @file_get_contents($path);
        if ($content === false) {
            $this->statusMessage = "Cannot read file: $path";
            return;
        }

        // Check if binary
        if (!mb_check_encoding($content, 'UTF-8')) {
            $this->viewBinaryFile($path, $content);
            return;
        }

        $lines = explode("\n", $content);
        $totalLines = count($lines);
        $viewerScroll = 0;
        $viewerSearch = '';
        $searchMatches = array();
        $currentMatch = -1;
        $viewerHeight = $this->termHeight - 4;
        $lineNumWidth = strlen((string)$totalLines) + 1;
        $running = true;

        // Detect language for syntax highlighting
        $language = $this->getLanguageFromExtension($path);

        // Full clear on first draw
        $this->fullClearScreen();

        while ($running) {
            // Check for terminal resize
            $this->checkResize();
            $viewerHeight = $this->termHeight - 4;
            $lineNumWidth = strlen((string)$totalLines) + 1;

            // Use output buffering for flicker-free redraw
            ob_start();

            // Move cursor home (don't clear)
            echo "\033[H";

            // Header
            $this->moveCursor(1, 1);
            echo self::COLOR_BG_BLUE . self::COLOR_WHITE . self::COLOR_BOLD;
            $header = " File: " . $this->truncate(basename($path), $this->termWidth - 30);
            $header .= str_repeat(' ', max(0, $this->termWidth - strlen($header) - 20));
            $header .= sprintf("Line %d-%d/%d ", $viewerScroll + 1, min($viewerScroll + $viewerHeight, $totalLines), $totalLines);
            echo str_pad($header, $this->termWidth);
            echo self::COLOR_RESET;

            // Content area
            for ($i = 0; $i < $viewerHeight; $i++) {
                $lineNum = $viewerScroll + $i;
                $this->moveCursor($i + 2, 1);

                // Clear the entire line first
                echo "\033[2K";

                if ($lineNum >= $totalLines) {
                    echo self::COLOR_DIM . '~' . self::COLOR_RESET;
                    continue;
                }

                // Line number
                echo self::COLOR_DIM;
                echo sprintf("%{$lineNumWidth}d ", $lineNum + 1);
                echo self::COLOR_RESET;

                $line = $lines[$lineNum];
                // Remove tabs and control characters that mess up display
                $line = str_replace("\t", "    ", $line);
                $displayWidth = $this->termWidth - $lineNumWidth - 2;

                // Truncate and pad line content
                $lineContent = substr($line, 0, $displayWidth);

                // Highlight search matches (takes priority over syntax highlighting)
                if (!empty($viewerSearch) && stripos($lineContent, $viewerSearch) !== false) {
                    $highlighted = preg_replace(
                        '/(' . preg_quote($viewerSearch, '/') . ')/i',
                        self::COLOR_BG_YELLOW . self::COLOR_BLACK . '$1' . self::COLOR_RESET,
                        $lineContent
                    );
                    echo $highlighted;
                } elseif ($language) {
                    // Apply syntax highlighting
                    echo $this->highlightSyntax($lineContent, $language);
                } else {
                    echo $lineContent;
                }
            }

            // Footer / status bar
            $this->moveCursor($this->termHeight - 1, 1);
            echo self::COLOR_BG_GRAY . self::COLOR_WHITE;
            if (!empty($viewerSearch)) {
                $matchInfo = count($searchMatches) > 0
                    ? sprintf(" Search: '%s' (%d/%d matches) ", $viewerSearch, $currentMatch + 1, count($searchMatches))
                    : sprintf(" Search: '%s' (no matches) ", $viewerSearch);
                echo str_pad($matchInfo, $this->termWidth);
            } else {
                echo str_pad(" " . $path, $this->termWidth);
            }
            echo self::COLOR_RESET;

            // Help line
            $this->moveCursor($this->termHeight, 1);
            echo "\033[2K"; // Clear line
            echo self::COLOR_DIM;
            echo " Arrows/jk:Scroll  u/d:Page  g/G:Start/End  /:Search  n/N:Match  ::Line  q:Close";
            echo self::COLOR_RESET;

            // Flush buffered output
            ob_end_flush();
            flush();

            // Handle input
            $key = $this->readKey();

            switch ($key) {
                case 'q':
                case 'Q':
                case "\033": // Escape
                    $running = false;
                    break;

                case "\033[A": // Up
                case 'k':      // Vim-style up (Mac-friendly)
                    if ($viewerScroll > 0) $viewerScroll--;
                    break;

                case "\033[B": // Down
                case 'j':      // Vim-style down (Mac-friendly)
                    if ($viewerScroll < $totalLines - 1) $viewerScroll++;
                    break;

                case "\033[5~": // Page Up
                case 'u':       // Page up (Mac-friendly)
                case "\025":    // Ctrl+U
                    $viewerScroll = max(0, $viewerScroll - $viewerHeight);
                    break;

                case "\033[6~": // Page Down
                case 'd':       // Page down (Mac-friendly)
                case "\004":    // Ctrl+D
                    $viewerScroll = min($totalLines - 1, $viewerScroll + $viewerHeight);
                    break;

                case "\033[H": // Home
                case "\033[1~":
                case 'g':       // Go to beginning (Mac-friendly, vim-style)
                    $viewerScroll = 0;
                    break;

                case "\033[F": // End
                case "\033[4~":
                case 'G':       // Go to end (Mac-friendly, vim-style)
                    $viewerScroll = max(0, $totalLines - $viewerHeight);
                    break;

                case '/': // Search
                    echo "\033[?25h";
                    $this->moveCursor($this->termHeight - 1, 1);
                    echo self::COLOR_BG_BLUE . self::COLOR_WHITE;
                    echo str_pad(" Search: ", $this->termWidth);
                    echo self::COLOR_RESET;
                    $this->moveCursor($this->termHeight - 1, 10);

                    system('stty sane');
                    $viewerSearch = trim(fgets(STDIN));
                    system('stty -icanon -echo');
                    echo "\033[?25l";

                    if (!empty($viewerSearch)) {
                        $searchMatches = array();
                        foreach ($lines as $idx => $line) {
                            if (stripos($line, $viewerSearch) !== false) {
                                $searchMatches[] = $idx;
                            }
                        }
                        if (count($searchMatches) > 0) {
                            $currentMatch = 0;
                            $viewerScroll = $searchMatches[0];
                        }
                    }
                    break;

                case 'n': // Next match
                    if (count($searchMatches) > 0) {
                        $currentMatch = ($currentMatch + 1) % count($searchMatches);
                        $viewerScroll = $searchMatches[$currentMatch];
                    }
                    break;

                case 'N': // Previous match
                    if (count($searchMatches) > 0) {
                        $currentMatch = ($currentMatch - 1 + count($searchMatches)) % count($searchMatches);
                        $viewerScroll = $searchMatches[$currentMatch];
                    }
                    break;

                case ':': // Go to line (vim-style command)
                    echo "\033[?25h";
                    $this->moveCursor($this->termHeight - 1, 1);
                    echo self::COLOR_BG_BLUE . self::COLOR_WHITE;
                    echo str_pad(" Go to line: ", $this->termWidth);
                    echo self::COLOR_RESET;
                    $this->moveCursor($this->termHeight - 1, 14);

                    system('stty sane');
                    $lineInput = trim(fgets(STDIN));
                    system('stty -icanon -echo');
                    echo "\033[?25l";

                    if (is_numeric($lineInput)) {
                        $targetLine = (int)$lineInput - 1;
                        $viewerScroll = max(0, min($totalLines - 1, $targetLine));
                    }
                    break;
            }
        }

        // Full clear before returning to file manager
        $this->fullClearScreen();
        $this->statusMessage = "Closed file: " . basename($path);
    }

    private function viewBinaryFile($path, $content)
    {
        $this->fullClearScreen();
        echo self::COLOR_BOLD . "Binary File: $path" . self::COLOR_RESET . "\n";
        echo str_repeat('-', $this->termWidth) . "\n";
        echo self::COLOR_YELLOW . "[Binary file - showing hex dump]" . self::COLOR_RESET . "\n\n";

        $bytes = substr($content, 0, 512);
        $offset = 0;
        foreach (str_split($bytes, 16) as $chunk) {
            echo self::COLOR_DIM . sprintf("%08X  ", $offset) . self::COLOR_RESET;

            $hex = '';
            $ascii = '';
            for ($i = 0; $i < 16; $i++) {
                if ($i < strlen($chunk)) {
                    $byte = ord($chunk[$i]);
                    $hex .= sprintf("%02X ", $byte);
                    $ascii .= ($byte >= 32 && $byte < 127) ? $chunk[$i] : '.';
                } else {
                    $hex .= '   ';
                }
                if ($i === 7) $hex .= ' ';
            }

            echo $hex . ' |' . $ascii . "|\n";
            $offset += 16;
        }

        echo "\n" . self::COLOR_DIM . "(Showing first 512 bytes)" . self::COLOR_RESET;
        echo "\n\n" . self::COLOR_INVERSE . " Press any key to continue... " . self::COLOR_RESET;
        $this->readKey();

        // Full clear before returning to file manager
        $this->fullClearScreen();
    }

    private function goUp()
    {
        $path = $this->panels[$this->activePanel]['path'];
        if ($path === '/') return;

        $parent = dirname($path);
        $this->panels[$this->activePanel]['path'] = $parent;
        $this->panels[$this->activePanel]['selected'] = 0;
        $this->panels[$this->activePanel]['scroll'] = 0;
        $this->loadDirectory($this->activePanel);
        $this->statusMessage = "Moved to: $parent";
    }

    private function copyFile()
    {
        $file = $this->getSelectedFile();
        if (!$file || $file['name'] === '..') {
            $this->statusMessage = "Cannot copy this item";
            return;
        }

        $source = $this->getSelectedPath();
        $otherPanel = 1 - $this->activePanel;
        $dest = $this->panels[$otherPanel]['path'] . '/' . $file['name'];

        if (file_exists($dest)) {
            $this->statusMessage = "Destination already exists: " . $file['name'];
            return;
        }

        if ($file['type'] === 'dir') {
            $this->statusMessage = "Directory copy not supported yet";
            return;
        }

        if (@copy($source, $dest)) {
            $this->statusMessage = "Copied: " . $file['name'];
            $this->loadDirectory($otherPanel);
        } else {
            $this->statusMessage = "Failed to copy: " . $file['name'];
        }
    }

    private function moveFile()
    {
        $file = $this->getSelectedFile();
        if (!$file || $file['name'] === '..') {
            $this->statusMessage = "Cannot move this item";
            return;
        }

        $source = $this->getSelectedPath();
        $otherPanel = 1 - $this->activePanel;
        $dest = $this->panels[$otherPanel]['path'] . '/' . $file['name'];

        if (file_exists($dest)) {
            $this->statusMessage = "Destination already exists: " . $file['name'];
            return;
        }

        if (@rename($source, $dest)) {
            $this->statusMessage = "Moved: " . $file['name'];
            $this->loadDirectory(0);
            $this->loadDirectory(1);
        } else {
            $this->statusMessage = "Failed to move: " . $file['name'];
        }
    }

    private function deleteFile()
    {
        $file = $this->getSelectedFile();
        if (!$file || $file['name'] === '..') {
            $this->statusMessage = "Cannot delete this item";
            return;
        }

        $path = $this->getSelectedPath();

        $this->moveCursor($this->termHeight - 2, 1);
        echo self::COLOR_BG_BLUE . self::COLOR_WHITE;
        echo str_pad(" Delete " . $file['name'] . "? (y/N) ", $this->termWidth);
        echo self::COLOR_RESET;

        $key = $this->readKey();

        if (strtolower($key) === 'y') {
            if ($file['type'] === 'dir') {
                if (@rmdir($path)) {
                    $this->statusMessage = "Deleted directory: " . $file['name'];
                    $this->loadDirectory($this->activePanel);
                } else {
                    $this->statusMessage = "Cannot delete (directory not empty?): " . $file['name'];
                }
            } else {
                if (@unlink($path)) {
                    $this->statusMessage = "Deleted: " . $file['name'];
                    $this->loadDirectory($this->activePanel);
                } else {
                    $this->statusMessage = "Failed to delete: " . $file['name'];
                }
            }
        } else {
            $this->statusMessage = "Delete cancelled";
        }
    }

    private function refresh()
    {
        $this->getTerminalSize();
        $this->panelWidth = (int)(($this->termWidth - 3) / 2);
        $this->contentHeight = $this->termHeight - 6;
        $this->loadDirectory(0);
        $this->loadDirectory(1);
        $this->statusMessage = "Refreshed";
    }

    private function readKey()
    {
        $stdin = fopen('php://stdin', 'r');
        stream_set_blocking($stdin, false);

        $c = null;

        // Use stream_select with timeout to allow periodic resize checks
        while ($c === null) {
            $read = array($stdin);
            $write = null;
            $except = null;

            // Wait up to 100ms for input, then check for resize
            $result = stream_select($read, $write, $except, 0, 100000);

            if ($result === false) {
                // Error occurred
                break;
            } elseif ($result > 0) {
                // Input available
                $c = fread($stdin, 1);
            } else {
                // Timeout - check for resize and redraw if needed
                $oldWidth = $this->termWidth;
                $oldHeight = $this->termHeight;
                $this->getTerminalSize();

                if ($this->termWidth !== $oldWidth || $this->termHeight !== $oldHeight) {
                    $this->panelWidth = (int)(($this->termWidth - 3) / 2);
                    $this->contentHeight = $this->termHeight - 6;
                    $this->fullClearScreen();
                    $this->draw();
                }
            }
        }

        // Handle escape sequences (arrow keys, function keys, etc.)
        if ($c === "\033") {
            // Set blocking temporarily for escape sequence
            stream_set_blocking($stdin, true);

            $next = fread($stdin, 1);
            if ($next === '[') {
                $c .= $next;
                $seq = '';
                // Read until we get a letter or ~
                while (true) {
                    $char = fread($stdin, 1);
                    $seq .= $char;
                    if (ctype_alpha($char) || $char === '~') {
                        break;
                    }
                }
                $c .= $seq;
            } else {
                // Plain escape key
                $c = "\033";
            }
        }

        fclose($stdin);
        return $c;
    }

    private function handleInput()
    {
        $key = $this->readKey();

        switch ($key) {
            case 'q':
            case 'Q':
                $this->running = false;
                break;
            case "\t": // Tab
                $this->activePanel = 1 - $this->activePanel;
                $this->statusMessage = "Switched to " . ($this->activePanel === 0 ? "left" : "right") . " panel";
                break;
            case "\033[A": // Up arrow
            case 'k':      // Vim-style up (Mac-friendly)
                $this->navigate(-1);
                break;
            case "\033[B": // Down arrow
            case 'j':      // Vim-style down (Mac-friendly)
                $this->navigate(1);
                break;
            case "\033[C": // Right arrow - switch panels
            case 'l':      // Vim-style right (Mac-friendly)
                $this->activePanel = 1;
                $this->statusMessage = "Switched to right panel";
                break;
            case "\033[D": // Left arrow - switch panels
            case 'h':      // Vim-style left (Mac-friendly)
                $this->activePanel = 0;
                $this->statusMessage = "Switched to left panel";
                break;
            case "\033[5~": // Page Up
            case 'u':       // Page up (Mac-friendly)
            case "\025":    // Ctrl+U
                $this->navigatePage(-1);
                break;
            case "\033[6~": // Page Down
            case 'd':       // Page down (Mac-friendly) - Note: changed delete to 'x'
            case "\004":    // Ctrl+D
                $this->navigatePage(1);
                break;
            case "\033[H": // Home
            case "\033[1~": // Home (alternative)
            case '0':       // Beginning (Mac-friendly, vim-style)
                $this->navigateToStart();
                break;
            case "\033[F": // End
            case "\033[4~": // End (alternative)
            case '$':       // End (Mac-friendly, vim-style)
                $this->navigateToEnd();
                break;
            case "\n": // Enter
            case "\r":
                $this->enterDirectory();
                break;
            case "\177": // Backspace
            case '-':    // Go up (Mac-friendly alternative)
                $this->goUp();
                break;
            case 'p':    // Go to path (changed from 'g' to avoid conflict with vim navigation)
            case 'P':
                $this->goToPath();
                break;
            case '/':
                $this->searchFiles();
                break;
            case 'n':
            case 'N':
                $this->nextSearchResult();
                break;
            case "\033": // Escape - clear search
                $this->clearSearch();
                break;
            case 'c':
            case 'C':
                $this->copyFile();
                break;
            case 'm':
            case 'M':
                $this->moveFile();
                break;
            case 'x':    // Delete (changed from 'd' to allow 'd' for page down)
            case 'X':
                $this->deleteFile();
                break;
            case 'r':
            case 'R':
                $this->refresh();
                break;
        }
    }

    public function run()
    {
        // Set terminal to raw mode
        system('stty -icanon -echo');

        // Switch to alternate screen buffer (prevents flicker)
        echo "\033[?1049h";

        // Hide cursor
        echo "\033[?25l";

        // Install signal handler for terminal resize (SIGWINCH)
        if (function_exists('pcntl_signal')) {
            declare(ticks = 1);
            pcntl_signal(SIGWINCH, array($this, 'handleResize'));
        }

        // Do initial full clear
        $this->fullClearScreen();

        $this->statusMessage = "Welcome to DualPane File Manager! Press 'q' to quit.";

        try {
            while ($this->running) {
                // Check for terminal resize on each loop (fallback method)
                $this->checkResize();

                $this->draw();
                $this->handleInput();
            }
        } catch (Exception $e) {
            // Restore terminal on error
        }

        // Switch back to main screen buffer
        echo "\033[?1049l";

        // Restore terminal
        system('stty sane');
        echo "\033[?25h"; // Show cursor
        echo "Thanks for using DualPane File Manager!\n";
    }

    public function handleResize($signo = null)
    {
        $this->getTerminalSize();
        $this->panelWidth = (int)(($this->termWidth - 3) / 2);
        $this->contentHeight = $this->termHeight - 6;
        $this->fullClearScreen();
        $this->statusMessage = "Terminal resized to {$this->termWidth}x{$this->termHeight}";
    }

    private $lastTermWidth = 0;
    private $lastTermHeight = 0;

    private function checkResize()
    {
        $oldWidth = $this->termWidth;
        $oldHeight = $this->termHeight;

        $this->getTerminalSize();

        if ($this->termWidth !== $oldWidth || $this->termHeight !== $oldHeight) {
            $this->panelWidth = (int)(($this->termWidth - 3) / 2);
            $this->contentHeight = $this->termHeight - 6;
            $this->fullClearScreen();
        }
    }
}

// Check if running in CLI
if (php_sapi_name() !== 'cli') {
    die("This application must be run from the command line.\n");
}

// Check for required functions
if (!function_exists('exec')) {
    die("The 'exec' function is required but disabled.\n");
}

$fm = new DualPaneFileManager();
$fm->run();
