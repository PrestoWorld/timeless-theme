// Mock for @wordpress/element
module.exports = {
  useState: (initial) => [initial, jest.fn()],
  useEffect: (callback) => callback(),
  useMemo: (factory) => factory(),
  createElement: (type, props, ...children) => ({ type, props, children }),
  Fragment: 'Fragment',
  useRef: () => ({ current: null }),
  useCallback: (fn) => fn,
  useContext: () => null,
};
