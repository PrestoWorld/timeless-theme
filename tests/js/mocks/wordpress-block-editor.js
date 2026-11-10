// Mock for @wordpress/block-editor
module.exports = {
  InspectorControls: 'InspectorControls',
  PanelBody: 'PanelBody',
  useBlockProps: (props = {}) => props,
  InnerBlocks: 'InnerBlocks',
  InnerBlocksContent: 'InnerBlocks.Content',
  BlockControls: 'BlockControls',
  BlockControlsButton: 'Button',
  useSelect: () => [],
  __experimentalUseSettings: () => ({}),
  withColors: (attributes) => (Component) => Component,
};
