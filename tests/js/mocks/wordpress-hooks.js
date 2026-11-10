// Mock for @wordpress/hooks
module.exports = {
  addFilter: jest.fn(),
  removeFilter: jest.fn(),
  doAction: jest.fn(),
  applyFilters: jest.fn((tag, value) => value),
  currentFilter: jest.fn(),
  didAction: jest.fn(),
  doAction: jest.fn(),
  removeAllFilters: jest.fn(),
  removeFilter: jest.fn(),
};
