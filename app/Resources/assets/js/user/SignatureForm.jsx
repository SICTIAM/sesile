import React, { Component } from 'react'
import { object, string, func } from 'prop-types'
import { translate } from 'react-i18next'

import { basicNotification } from '../_components/Notifications'
import {InputFile} from '../_components/Form'
import { Cell, GridX } from "../_components/UI"

import { handleErrors } from '../_utils/Utils'

class SignatureForm extends Component {
    static contextTypes = {
        t: func,
        _addNotification: func
    }
    static defaultProps = {
        user: {
            path_signature: ""
        }
    }
    putFileSignature = (image) => {
        const { t, _addNotification } = this.context
        let formData  = new FormData()
        formData.append('signatures', image)

        fetch(Routing.generate("sesile_user_userapi_uploadsignature", {id: this.props.user.id}), {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(response => response.json())
            .then(user => {
                _addNotification(basicNotification(
                    'success',
                    t('admin.success.add', {name: t('admin.user.image_signature')})
                ))
                console.log(this.props.user.id)
                this.props.handleChangeUser(user)
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.add', {name: t('admin.user.image_signature'), errorCode: error.status}),
                error.statusText)))
    }

    deleteFileSignature = (userId) => {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_user_userapi_deletesignature', {id: userId}), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(response => response.json())
            .then(user => {
                _addNotification(basicNotification(
                    'success',
                    t('admin.success.delete', {name: t('admin.user.image_signature')})
                ))
                //this.props.handleChangeUser(user)
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_removable', {name: t('admin.user.image_signature'), errorCode: error.status}),
                error.statusText)))
    }

    render() {
        const { t } = this.context
        const { styleClass } = this.props
        return(
            <div className={styleClass}>
                <div className="grid-x grid-margin-x align-middle">
                    <label className="cell medium-2 text-bold text-capitalize" htmlFor="signature_img">
                        {t('admin.user.image_signature')}
                    </label>
                    <div className="cell medium-4">
                        {this.props.user.path_signature &&
                            <Cell className="align-center-middle">
                                <img
                                    id="signature_img"
                                    src={"/uploads/signatures/" + this.props.user.path_signature} />
                            </Cell>}
                    </div>
                    <div className="cell medium-6">
                        <div className="grid-x grid-margin-x">
                            <InputFile  id="add_signature_img"
                                        className="cell medium-6"
                                        labelText=
                                            {this.props.user.path_signature ?
                                                t('common.button.change_img') :
                                                t('common.button.upload_img')}
                                        accept="image/png,image/jpeg"
                                        onChange={this.putFileSignature}/>
                            {this.props.user.path_signature &&
                                <Cell className="medium-6">
                                        <button
                                            className="button alert text-uppercase hollow"
                                            onClick={() => this.deleteFileSignature(this.props.user.id)}>
                                            {t('common.button.delete')}
                                        </button>
                                </Cell>}
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}


SignatureForm.PropTypes = {
    user: object.isRequired,
    styleClass: string
}

export default translate(['sesile'])(SignatureForm)