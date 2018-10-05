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

    render () {
        const { t } = this.context
        const { roles, changeUserRole, removeUserRole, addUserRole } = this.props

        return (
            <div className="medium-12 cell">
                { roles.map((role, key) =>
                    (
                        <div key={key} className="grid-x align-middle">
                            <InputValidation    id={key}
                                                type="text"
                                                className="cell medium-9"
                                                labelText={t('admin.user.label_role_user')}
                                                value={role.user_roles}
                                                onChange={changeUserRole}
                                                validationRule='required'
                                                placeholder={t('admin.user.placeholder_role_user')}
                            />
                            <div className="cell medium-1 text-center align-center-middle">
                                <button className="fa fa-trash medium icon-action" onClick={() => removeUserRole(key)}></button>
                            </div>
                        </div>
                    )
                )}
                <div className="grid-x">
                    <div className="medium-6">
                        <button className="primary button float-right text-uppercase" onClick={addUserRole}>{t('common.button.add')}</button>
                    </div>
                </div>
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