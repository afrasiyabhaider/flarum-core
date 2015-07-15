import Component from 'flarum/Component';
import Alert from 'flarum/components/Alert';

/**
 * The `Alerts` component provides an area in which `Alert` components can be
 * shown and dismissed.
 */
export default class Alerts extends Component {
  constructor(...args) {
    super(...args);

    /**
     * An array of Alert components which are currently showing.
     *
     * @type {Alert[]}
     * @protected
     */
    this.components = [];
  }

  view() {
    return (
      <div className="alerts">
        {this.components.map(component => <div className="alerts-item">{component}</div>)}
      </div>
    );
  }

  /**
   * Show an Alert in the alerts area.
   *
   * @param {Alert} component
   * @public
   */
  show(component) {
    if (!(component instanceof Alert)) {
      throw new Error('The Alerts component can only show Alert components');
    }

    component.props.ondismiss = this.dismiss.bind(this, component);

    this.components.push(component);
    m.redraw();
  }

  /**
   * Dismiss an alert.
   *
   * @param {Alert} component
   * @public
   */
  dismiss(component) {
    const index = this.components.indexOf(component);

    if (index !== -1) {
      this.components.splice(index, 1);
      m.redraw();
    }
  }

  /**
   * Clear all alerts.
   *
   * @public
   */
  clear() {
    this.components = [];
    m.redraw();
  }
}
