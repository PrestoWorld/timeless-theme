// Mock for html-react-parser
module.exports = {
  parse: (html) => ({ type: 'parsed-html', html }),
  attributesToProps: (attrs) => attrs || {},
  domToReact: (dom) => ({ type: 'dom-to-react', dom }),
  convertNodeToElement: (node) => node,
};
