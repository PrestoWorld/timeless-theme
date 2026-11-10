// Mock for react
module.exports = {
  createElement: (type, props, ...children) => ({ type, props, children }),
  Fragment: 'Fragment',
  useState: (initial) => [initial, jest.fn()],
  useEffect: (callback) => callback(),
  useMemo: (factory) => factory(),
  useRef: () => ({ current: null }),
  useCallback: (fn) => fn,
  useContext: () => null,
  Children: {
    map: (children, fn) => (Array.isArray(children) ? children.map(fn) : []),
    forEach: () => {},
    count: () => 0,
    only: () => null,
    toArray: () => [],
  },
  Component: class {},
  PureComponent: class {},
  memo: (c) => c,
  forwardRef: (c) => c,
};
