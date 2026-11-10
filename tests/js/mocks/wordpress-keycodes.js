// Mock for @wordpress/keycodes
module.exports = {
  DOWN: 'ArrowDown',
  UP: 'ArrowUp',
  LEFT: 'ArrowLeft',
  RIGHT: 'ArrowRight',
  displayShortcut: (shortcut) => shortcut,
  isKeyboardEvent: (event) => event && typeof event.key === 'string',
};
