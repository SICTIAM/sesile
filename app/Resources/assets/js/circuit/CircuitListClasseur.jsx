import React, {Component} from 'react'
import PropTypes from 'prop-types'
import CircuitClasseurDropdown from './CircuitClasseurDropdown'

class CircuitListClasseur extends Component {

    constructor(props) {
        super(props)
    }

    render () {
        const { classeurId, etape_classeurs, user } = this.props

        return (
            <div className="dropdown-pane dropdown-pane-circuit top" id={"example-dropdown-" + classeurId} data-dropdown data-hover="true" data-hover-pane="true">
                <CircuitClasseurDropdown classeurId={classeurId} etape_classeurs={etape_classeurs} user={user} />
            </div>
        )
    }
}

CircuitListClasseur.PropTypes = {
    classeurId: PropTypes.number.isRequired,
    etape_classeurs: PropTypes.object.isRequired,
    user: PropTypes.object.isRequired,
    align: PropTypes.string.isRequired
}

export default CircuitListClasseur