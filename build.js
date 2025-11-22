#!/usr/bin/env node

const fs = require('fs');
const path = require('path');

const SRC_DIR = 'src';
const DIST_DIR = 'dist';

// Directories to skip (handled specially or not needed in dist)
const SKIP_DIRS = [
    'scss',      // Compiled to CSS
    'js',        // Bundled to bundle.js
    'css',       // Old compiled files
    'node_modules',
    '.git',
    '.vscode',
    '.idea'
];

// Files/patterns to skip
const SKIP_FILES = [
    '.DS_Store',
    'Thumbs.db',
    '.gitkeep',
    /^\..*$/      // Hidden files
];

// JS files to bundle - priority order (these load first)
// Any other .js files in src/js/ will be bundled after these
const JS_FILES_PRIORITY = [
    'color-extractor.js',  // Must load first (used by carousel)
    'color-namer.js',      // Must load before carousel (used by carousel)
    'carousel.js',
    'particles.js',
    'tooltips.js',
    'main-nav.js',
    'modal-manager.js',    // Must load before modal.js and admin.js
    'form-handler.js',     // Must load before modal.js and admin.js
    'editor-factory.js',   // Must load before modal.js and admin.js
    'category-handler.js', // Must load before modal.js and admin.js
    'image-preview.js',    // Must load before modal.js and admin.js
    'modal.js',
    'bg-rotator.js'
];

/**
 * Check if a file should be skipped
 */
function shouldSkipFile(filename) {
    // Always include .env file (needed for environment variables)
    if (filename === '.env') {
        return false;
    }

    return SKIP_FILES.some(pattern => {
        if (pattern instanceof RegExp) {
            return pattern.test(filename);
        }
        return filename === pattern;
    });
}

/**
 * Get all directories to copy from src/
 */
function getDirsToCopy() {
    if (!fs.existsSync(SRC_DIR)) {
        return [];
    }

    return fs.readdirSync(SRC_DIR, { withFileTypes: true })
        .filter(entry => entry.isDirectory())
        .filter(entry => !SKIP_DIRS.includes(entry.name))
        .map(entry => entry.name);
}

/**
 * Get all root-level files to copy from src/
 */
function getFilesToCopy() {
    if (!fs.existsSync(SRC_DIR)) {
        return [];
    }

    return fs.readdirSync(SRC_DIR, { withFileTypes: true })
        .filter(entry => entry.isFile())
        .filter(entry => !shouldSkipFile(entry.name))
        .map(entry => entry.name);
}

/**
 * Get all JS files to bundle
 * Returns priority files first, then any other .js files found
 */
function getAllJSFiles() {
    const jsDir = path.join(SRC_DIR, 'portfolio', 'js');

    if (!fs.existsSync(jsDir)) {
        return JS_FILES_PRIORITY;
    }

    // Get all .js files in src/js/
    const allFiles = fs.readdirSync(jsDir)
        .filter(file => file.endsWith('.js'))
        .sort();

    // Start with priority files
    const orderedFiles = [...JS_FILES_PRIORITY];

    // Add any files not in priority list
    allFiles.forEach(file => {
        if (!orderedFiles.includes(file)) {
            orderedFiles.push(file);
        }
    });

    return orderedFiles;
}

/**
 * Recursively copy directory
 */
function copyDirectory(src, dest) {
    // Create destination directory if it doesn't exist
    if (!fs.existsSync(dest)) {
        fs.mkdirSync(dest, { recursive: true });
    }

    const entries = fs.readdirSync(src, { withFileTypes: true });

    for (const entry of entries) {
        const srcPath = path.join(src, entry.name);
        const destPath = path.join(dest, entry.name);

        if (entry.isDirectory()) {
            // Skip directories that are in SKIP_DIRS
            if (SKIP_DIRS.includes(entry.name)) {
                continue;
            }
            copyDirectory(srcPath, destPath);
        } else {
            // Skip files that should be skipped
            if (shouldSkipFile(entry.name)) {
                continue;
            }
            fs.copyFileSync(srcPath, destPath);
        }
    }
}

/**
 * Copy a single file
 */
function copyFile(filename) {
    const srcPath = path.join(SRC_DIR, filename);
    const destPath = path.join(DIST_DIR, filename);

    if (fs.existsSync(srcPath)) {
        fs.copyFileSync(srcPath, destPath);
        console.log(`✓ Copied: ${filename}`);
    } else {
        console.log(`⚠ Not found: ${filename}`);
    }
}

/**
 * Bundle JavaScript files
 */
function bundleJS() {
    console.log('\n📦 Bundling JavaScript...');

    const jsDir = path.join(SRC_DIR, 'portfolio', 'js');
    const distJsDir = path.join(DIST_DIR, 'portfolio', 'js');

    // Create dist/js directory if it doesn't exist
    if (!fs.existsSync(distJsDir)) {
        fs.mkdirSync(distJsDir, { recursive: true });
    }

    // Get all JS files (priority files first, then any others)
    const jsFiles = getAllJSFiles();

    let bundleContent = '';

    // Add header comment
    bundleContent += '/**\n';
    bundleContent += ' * Bundled JavaScript\n';
    bundleContent += ` * Generated: ${new Date().toISOString()}\n`;
    bundleContent += ' * Source files:\n';
    jsFiles.forEach(file => {
        bundleContent += ` * - ${file}\n`;
    });
    bundleContent += ' */\n\n';

    // Bundle each file
    jsFiles.forEach(file => {
        const filePath = path.join(jsDir, file);

        if (fs.existsSync(filePath)) {
            const content = fs.readFileSync(filePath, 'utf8');
            bundleContent += `\n/* ========================================\n`;
            bundleContent += ` * ${file}\n`;
            bundleContent += ` * ======================================== */\n\n`;
            bundleContent += content;
            bundleContent += '\n\n';
            console.log(`  ✓ Bundled: ${file}`);
        } else {
            console.log(`  ⚠ Not found: ${file}`);
        }
    });

    // Write bundle
    const bundlePath = path.join(distJsDir, 'bundle.js');
    fs.writeFileSync(bundlePath, bundleContent);
    console.log(`\n✓ Created: dist/portfolio/js/bundle.js (${jsFiles.length} files)`);
}

/**
 * Main build function
 */
function build() {
    console.log('🔨 Building project...\n');

    // Ensure dist directory exists
    if (!fs.existsSync(DIST_DIR)) {
        fs.mkdirSync(DIST_DIR, { recursive: true });
    }

    // Get all directories and files to copy (automatically discovered)
    const dirsToCopy = getDirsToCopy();
    const filesToCopy = getFilesToCopy();

    // Copy directories
    console.log('📁 Copying directories...');
    dirsToCopy.forEach(dir => {
        const srcPath = path.join(SRC_DIR, dir);
        const destPath = path.join(DIST_DIR, dir);

        copyDirectory(srcPath, destPath);
        console.log(`✓ Copied directory: ${dir}/`);
    });

    // Copy files
    console.log('\n📄 Copying files...');
    filesToCopy.forEach(file => {
        copyFile(file);
    });

    // Copy .env file to dist for production deployment
    console.log('\n📝 Copying config files...');
    const envPath = path.join('.', '.env');
    const envDestPath = path.join(DIST_DIR, '.env');
    if (fs.existsSync(envPath)) {
        fs.copyFileSync(envPath, envDestPath);
        console.log('✓ Copied: .env');
    } else {
        console.log('⚠ .env file not found (not required for build)');
    }

    // Copy .htaccess file for Apache configuration
    const htaccessPath = path.join('.', '.htaccess');
    const htaccessDestPath = path.join(DIST_DIR, '.htaccess');
    if (fs.existsSync(htaccessPath)) {
        fs.copyFileSync(htaccessPath, htaccessDestPath);
        console.log('✓ Copied: .htaccess');
    } else {
        console.log('⚠ .htaccess file not found');
    }

    // Bundle JavaScript
    bundleJS();

    console.log('\n✅ Build complete!\n');
}

/**
 * Watch mode
 */
function watch() {
    console.log('👀 Watching for changes in src/...\n');

    // Initial build
    build();

    // Watch src directory
    fs.watch(SRC_DIR, { recursive: true }, (_eventType, filename) => {
        if (filename) {
            console.log(`\n📝 Change detected: ${filename}`);

            // Check if it's a JS file
            if (filename.startsWith('portfolio/js/') && filename.endsWith('.js')) {
                console.log('🔄 Re-bundling JavaScript...');
                bundleJS();
                return;
            }

            const srcPath = path.join(SRC_DIR, filename);
            const destPath = path.join(DIST_DIR, filename);

            // Check if it's a file or directory
            if (fs.existsSync(srcPath)) {
                const stats = fs.statSync(srcPath);

                if (stats.isFile()) {
                    // Create parent directory if needed
                    const destDir = path.dirname(destPath);
                    if (!fs.existsSync(destDir)) {
                        fs.mkdirSync(destDir, { recursive: true });
                    }

                    fs.copyFileSync(srcPath, destPath);
                    console.log(`✓ Updated: ${filename}`);
                }
            } else {
                // File was deleted
                if (fs.existsSync(destPath)) {
                    fs.unlinkSync(destPath);
                    console.log(`✓ Deleted: ${filename}`);
                }
            }
        }
    });

    console.log('Press Ctrl+C to stop watching');
}

// Check for --watch flag
const isWatchMode = process.argv.includes('--watch');

if (isWatchMode) {
    watch();
} else {
    build();
}
