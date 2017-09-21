import React, { Component } from 'react'
import PropTypes from 'prop-types'
import ListClasseurs from '../classeur/ListClasseurs'

class UserListClasseurs extends Component {

    constructor(props) {
        super(props);
        this.state = {
            match: null,
            userId: this.props.match.params.userId
        }
    }

    render(){
        return (
            <ListClasseurs userId={this.state.userId} />
        )
    }
}

UserListClasseurs.PropTypes = {
    match: PropTypes.object.isRequired
}

export default UserListClasseurs