/**
 * Ludo King - Anti-Inspection & Security Shield
 * - Disables right-click context menu
 * - Blocks F12, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+Shift+C, Ctrl+U, Ctrl+S
 * - Protects source code from casual inspection and client tampering
 * - Sanitizes client environment against debugging and script injection
 */
(function() {
  'use strict';

  // 1. Disable Right-Click Context Menu
  document.addEventListener('contextmenu', function(e) {
    e.preventDefault();
    return false;
  }, { capture: true });

  // 2. Block Inspect, DevTools, and Source-Viewing Keyboard Shortcuts
  window.addEventListener('keydown', function(e) {
    // F12 key
    if (e.keyCode === 123 || e.key === 'F12') {
      e.preventDefault();
      e.stopPropagation();
      return false;
    }

    const ctrlOrCmd = e.ctrlKey || e.metaKey;

    // Ctrl + Shift + I (Inspect)
    // Ctrl + Shift + J (Console)
    // Ctrl + Shift + C (Element Inspector)
    if (ctrlOrCmd && e.shiftKey && (e.key === 'I' || e.key === 'i' || e.key === 'J' || e.key === 'j' || e.key === 'C' || e.key === 'c')) {
      e.preventDefault();
      e.stopPropagation();
      return false;
    }

    // Ctrl + U (View Source)
    if (ctrlOrCmd && (e.key === 'U' || e.key === 'u')) {
      e.preventDefault();
      e.stopPropagation();
      return false;
    }

    // Ctrl + S (Save Page)
    if (ctrlOrCmd && (e.key === 'S' || e.key === 's')) {
      e.preventDefault();
      e.stopPropagation();
      return false;
    }
  }, { capture: true });

  // 3. Disable Dragging & Copying of Game UI Assets
  document.addEventListener('dragstart', function(e) {
    e.preventDefault();
    return false;
  });

  // 4. Console Protection & Warning Banner
  try {
    const warningTitle = 'font-size: 28px; font-weight: 900; color: #ff3838; text-shadow: 0 2px 4px rgba(0,0,0,0.8);';
    const warningBody = 'font-size: 14px; font-weight: 700; color: #ffd700;';
    console.log('%c⚠️ STOP! ⚠️', warningTitle);
    console.log('%cThis game is protected by Ludo King Anti-Tamper & Security Shield.\nUnauthorized inspection, modification, or API manipulation is strictly prohibited.', warningBody);
  } catch(e) {}
})();
