import React, { Component } from 'react'
import { object, string, func } from 'prop-types'
import UserAvatar from 'react-user-avatar'
import { handleErrors } from '../_utils/Utils'
import { translate } from 'react-i18next'
import { basicNotification } from '../_components/Notifications'
import {InputFile} from '../_components/Form'

class AvatarForm extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }
    static defaultProps = {
        user: {
            _nom: "",
            _prenom: ""
        }
    }
    putFile = (image) => {
        const { t, _addNotification } = this.context
        let formData  = new FormData()
        formData.append('path', image)

        fetch(Routing.generate("sesile_user_userapi_uploadavatar", {id: this.props.user.id}), {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(response => response.json())
            .then(user => {
                _addNotification(basicNotification(
                    'success',
                    t('admin.success.update', {name: t('admin.user.image_avatar')})
                ))
                this.props.handleChangeUser(user)
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.add', {name: t('admin.user.image_avatar'), errorCode: error.status}),
                error.statusText)))
    }

    deleteFile = (userId) => {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_user_userapi_deleteavatar', {id: userId}), {
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
                    t('admin.success.delete', {name: t('admin.user.image_avatar')})
                ))
                this.props.handleChangeUser(user)
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_removable', {name: t('admin.user.image_avatar'), errorCode: error.status}),
                error.statusText)))

    }
    userNomAndPrenomIsNotEmpty = () => this.props.user._nom.length > 0 && this.props.user._prenom.length > 0

    userNomAndPrenomAndImagePathIsNotEmpty = () => this.userNomAndPrenomIsNotEmpty() && this.props.user.path.length > 0

    render() {
        const { t } = this.context
        const { styleClass } = this.props
        return(
            <div className={styleClass}>
                <div className="grid-x grid-padding-x align-middle">
                    <label className="cell medium-2 text-bold text-capitalize-first-letter" htmlFor="profil_img">
                        {t('admin.user.image_avatar')}
                    </label>
                    <div className="cell medium-4">
                        {this.userNomAndPrenomAndImagePathIsNotEmpty() ?
                            <UserAvatar
                                id="profil_img"
                                size="70"
                                name={`${this.props.user._prenom.charAt(0)}${this.props.user._nom.charAt(0)} `}
                                src={"/uploads/avatars/" + this.props.user.path}
                                className=" float-center" /> :
                            this.userNomAndPrenomIsNotEmpty() &&
                                <UserAvatar
                                    id="profil_img"
                                    size="70"
                                    name={`${this.props.user._prenom.charAt(0)}${this.props.user._nom.charAt(0)} `}
                                    className="txt-avatar"/>}
                    </div>
                    <div className="cell medium-6">
                        <div className="grid-x grid-margin-x">
                            <InputFile
                                id="add_avatar_img"
                                className="cell medium-6"
                                labelText=
                                    {this.props.user.path ?
                                        t('common.button.change_img') :
                                        t('common.button.upload_img')}
                                accept="image/png,image/jpeg"
                                onChange={this.putFile}/>
                            {this.props.user.path &&
                            <div className="cell medium-6">
                                <button
                                    className="button alert text-uppercase hollow"
                                    onClick={() => this.deleteFile(this.props.user.id)}>
                                    {t('common.button.delete')}
                                </button>
                            </div>}
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}


AvatarForm.PropTypes = {
    user: object.isRequired,
    styleClass: string
}

export default translate(['sesile'])(AvatarForm)