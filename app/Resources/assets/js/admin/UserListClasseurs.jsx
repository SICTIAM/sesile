import React, { Component } from 'react'
import PropTypes from 'prop-types'
import ClasseursList from '../classeur/ClasseursList'

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
            <ClasseursList user={this.props.user} userId={this.state.userId} />
        )
    }
}

UserListClasseurs.PropTypes = {
    match: PropTypes.object.isRequired
}

export default UserListClasseurs