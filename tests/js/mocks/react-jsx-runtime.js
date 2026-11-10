// Mock for react/jsx-runtime (automatic JSX runtime)
module.exports = {
  jsx: (type, props) => ({ type, props, _jsx: true }),
  jsxs: (type, props, children) => ({ type, props, children, _jsxs: true }),
  Fragment: Symbol('react.fragment'),
};
