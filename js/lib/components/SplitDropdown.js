import Dropdown from 'flarum/components/Dropdown';
import Button from 'flarum/components/Button';
import icon from 'flarum/helpers/icon';

/**
 * The `SplitDropdown` component is similar to `Dropdown`, but the first child
 * is displayed as its own button prior to the toggle button.
 */
export default class SplitDropdown extends Dropdown {
  static initProps(props) {
    super.initProps(props);

    props.className += ' split-dropdown';
    props.menuClassName += ' dropdown-menu-right';
  }

  getButton() {
    // Make a copy of the props of the first child component. We will assign
    // these props to a new button, so that it has exactly the same behaviour as
    // the first child.
    const firstChild = this.getFirstChild();
    const buttonProps = Object.assign({}, firstChild.props);
    buttonProps.className = (buttonProps.className || '') + ' ' + this.props.buttonClassName;

    return [
      Button.component(buttonProps),
      <a href="javascript:;"
        className={'dropdown-toggle btn-icon ' + this.props.buttonClassName}
        data-toggle="dropdown">
        {icon(this.props.icon)}
        {icon('caret-down', {className: 'caret'})}
      </a>
    ];
  }

  /**
   * Get the first child. If the first child is an array, the first item in that
   * array will be returned.
   *
   * @return {*}
   * @protected
   */
  getFirstChild() {
    let firstChild = this.props.children;

    while (firstChild instanceof Array) firstChild = firstChild[0];

    return firstChild;
  }
}
