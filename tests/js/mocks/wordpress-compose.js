// Mock for @wordpress/compose
module.exports = {
  useViewportMatch: () => false,
  useIsomorphicLayoutEffect: (fn) => fn(),
};
