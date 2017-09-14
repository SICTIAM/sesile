import React, { Component }from 'react'
import PropTypes from 'prop-types'

class Groups extends Component {
    constructor(props) {
        super(props)
        this.state = {
            user: {}
        }
    }

    render() {
        return (
            <div className="user-group">
                <h4 className="text-center text-bold">Rechercher votre groupe d'utilisateurs</h4>
                <p className="text-center">Puis accéder aux paramétres</p>
            </div>
        )
    }
}

const { object } = PropTypes

Groups.PropTypes = {
    user: object.isRequired
}

export default Groups