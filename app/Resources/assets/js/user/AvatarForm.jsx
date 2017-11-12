import React, { Component } from 'react'
import { object, string, func } from 'prop-types'
import UserAvatar from 'react-user-avatar'
import { translate } from 'react-i18next'
import { basicNotification } from '../_components/Notifications'

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

    handleErrors(response) {
        if (response.ok) {
            return response
        }
        throw response
    }

    componentWillMount() {
        const { user } = this.props
        this.setState({user: user})
    }

    putFile = (image, userId) => {
        const { t, _addNotification } = this.context
        let formData  = new FormData()
        formData.append('path', image)

        fetch(Routing.generate("sesile_user_userapi_uploadavatar", {id: userId}), {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(this.handleErrors)
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
            .then(this.handleErrors)
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
                            <UserAvatar size="100" name={ user._prenom } src={"/uploads/avatars/" + user.path}/>
                            : <UserAvatar size="100" name={ user._prenom } className="txt-avatar"/>

                    }
                    <input type="file" name="file" onChange={(e) => this.putFile(e.target.files[0], user.id)}/>
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