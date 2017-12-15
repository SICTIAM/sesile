import React, {Component} from 'react'
import PropTypes from 'prop-types'
import CircuitClasseur from './CircuitClasseur'

class CircuitListClasseur extends Component {

    constructor(props) {
        super(props)
    }

    render () {

        return (
            <div className="dropdown-pane dropdown-pane-circuit" id={"example-dropdown-" + this.props.classeurId} data-dropdown data-hover="true" data-hover-pane="true">
                <div className="grid-x grid-margin-y">
                    <div className="cell medium-12">
                        <h3>circuit de validation</h3>
                    </div>
                </div>

                <CircuitClasseur classeurId={this.props.classeurId} etape_classeurs={this.props.etape_classeurs} user={this.props.user} />

            </div>
        )
    }
}

CircuitListClasseur.PropTypes = {
    classeurId: PropTypes.number.isRequired,
    etape_classeurs: PropTypes.object.isRequired,
    user: PropTypes.object.isRequired,
}

export default CircuitListClasseur