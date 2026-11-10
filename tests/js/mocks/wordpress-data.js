// Mock for @wordpress/data
module.exports = {
  useSelect: () => [],
  useDispatch: () => ({}),
  registerStore: jest.fn(),
};
