import React, { Component } from 'react'
import { number, func, array } from 'prop-types'
import { translate } from 'react-i18next'
import {Button, Form, Select, Textarea} from '../_components/Form'
import InputValidation from '../_components/InputValidation'

class RolesUser extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }
    constructor(props) {
        super(props)
        this.state = {
            value: '',
            userId: ''
        }
        this.handleChange = this.handleChange.bind(this);
        this.handleSubmit = this.handleSubmit.bind(this);
    }

    handleChange(event) {
        this.setState({value: event.target.value});
        console.log(this.state.value)
    }

    handleSubmit(event) {
        this.props.addUserRole(this.state.value)
        this.setState({value: ''})
        event.preventDefault();
    }

    render () {
        const { t } = this.context
        const { roles, changeUserRole, removeUserRole, addUserRole } = this.props

        return (
            <div className="medium-12 cell">
                <label className="text-bold text-capitalize-first-letter">Rôles</label>
                <div className="grid-x">
                    <div className="medium-9">
                        <form onSubmit={this.handleSubmit} style={{display:"flex"}}>
                            <input type="text" value={this.state.value} onChange={this.handleChange} />
                            <button type="submit" style={{marginLeft:"0.6em", marginBottom:"0.6em"}}><i className="fa fa-plus-circle icon-action"></i></button>
                        </form>
                    </div>
                </div>
                { roles.map((role, key) =>
                    (
                        <div key={key} className="grid-x align-middle">
                            <ul className="cell medium-7" id={key}>{role.user_roles}</ul>
                            <div className="cell medium-1 text-center align-center-middle" style={{marginBottom:"0.6em"}}>
                                <button className="fa fa-minus-circle medium icon-action float-right" onClick={() => removeUserRole(key)}></button>
                            </div>
                        </div>
                    )
                )}
            </div>
        )
    }
}

RolesUser.PropTypes = {
    roles: array.isRequired,
    changeUserRole: func,
    removeUserRole: func,
    addUserRole: func
}

export default translate(['sesile'])(RolesUser)