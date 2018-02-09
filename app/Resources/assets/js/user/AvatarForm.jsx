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

    putFile = (image) => {
        const { t, _addNotification } = this.context
        const { user } = this.state
        let formData  = new FormData()
        formData.append('path', image)

        fetch(Routing.generate("sesile_user_userapi_uploadavatar", {id: user.id}), {
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
                this.setState({user})
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
                this.setState({user})
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_removable', {name: t('admin.user.image_avatar'), errorCode: error.status}),
                error.statusText)))

    }

    render() {
        const { t } = this.context
        const { styleClass } = this.props
        const { user } = this.state

        return(
            <div className={styleClass}>
                <div>
                    {
                        user.path ?
                            <UserAvatar size="100" name={ user._prenom || user._nom || '' } src={"/uploads/avatars/" + user.path} className=" float-center" />
                            : <UserAvatar size="100" name={ user._prenom || user._nom || '' } className="txt-avatar"/>

                    }
                    <InputFile  id="add_avatar_img"
                                className="cell medium-2"
                                labelText={user.path ? t('common.button.change_img') : t('common.button.upload_img')}
                                accept="image/png,image/jpeg"
                                onChange={this.putFile}/>

                    {user.path && <button className="button alert text-uppercase"
                                          onClick={() => this.deleteFile(user.id)}>{t('common.button.delete')}</button>}
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