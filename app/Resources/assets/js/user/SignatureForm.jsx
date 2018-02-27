import React, { Component } from 'react'
import { object, string, func } from 'prop-types'
import { translate } from 'react-i18next'
import { basicNotification } from '../_components/Notifications'
import { handleErrors } from '../_utils/Utils'
import {InputFile} from '../_components/Form'
import { Cell, GridX } from "../_components/UI"

class SignatureForm extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }

    constructor(props) {
        super(props)
        this.state = {
            user: {
                _nom: "",
                _prenom: "avatar"
            }
        }
    }

    componentWillMount() {
        const { user } = this.props
        this.setState({user: user})
    }


    putFileSignature = (image) => {
        const { t, _addNotification } = this.context
        const { user } = this.state
        let formData  = new FormData()
        formData.append('signatures', image)

        fetch(Routing.generate("sesile_user_userapi_uploadsignature", {id: user.id}), {
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
                this.setState({user})
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
                this.setState({user})
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_removable', {name: t('admin.user.image_signature'), errorCode: error.status}),
                error.statusText)))

    }

    render() {
        const { t } = this.context
        const { styleClass } = this.props
        const { user } = this.state

        return(
            <div className={styleClass}>
                <div className="grid-x grid-margin-x">
                    <Cell>
                        <div className="grid-y grid-margin-y">
                            {user.path_signature &&
                                <Cell className="align-center-middle">
                                    <img src={"/uploads/signatures/" + user.path_signature} />
                                </Cell>}
                            <Cell>
                                <p className="help-text">{this.props.helpText}</p>
                            </Cell>
                        </div>
                    </Cell>
                    <Cell>
                        <div className="grid-x grid-margin-x">
                            <InputFile  id="add_signature_img"
                                        className="cell medium-6"
                                        labelText={user.path ? t('common.button.change_img') : t('common.button.upload_img')}
                                        accept="image/png,image/jpeg"
                                        onChange={this.putFileSignature}/>
                            {user.path_signature &&
                                <Cell className="medium-6">

                                        <button
                                            className="button alert text-uppercase"
                                            onClick={() => this.deleteFileSignature(user.id)}>
                                            {t('common.button.delete')}
                                        </button>
                                </Cell>}
                        </div>
                    </Cell>
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