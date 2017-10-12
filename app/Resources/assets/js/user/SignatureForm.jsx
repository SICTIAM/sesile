import React, { Component } from 'react'
import { object, string, func } from 'prop-types'
import { translate } from 'react-i18next'
import { basicNotification } from '../_components/Notifications'

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


    putFileSignature = (image, userId) => {
        const { t, _addNotification } = this.context
        let formData  = new FormData()
        formData.append('signatures', image)

        fetch(Routing.generate("sesile_user_userapi_uploadsignature", {id: userId}), {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(this.handleErrors)
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
                t('admin.error.not_addable', {name: t('admin.user.image_signature'), errorCode: error.status}),
                error.statusText)))
    }

    deleteFileSignature = (userId) => {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_user_userapi_deletesignature', {id: userId}), {
            method: 'POST',
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
                {
                    user.path_signature &&
                    <div className="grid-x grid-padding-x align-center-middle">
                        <img className="medium-4 cell" src={"/uploads/signatures/" + user.path_signature} />
                    </div>
                }
                <input type="file" onChange={(e) => this.putFileSignature(e.target.files[0], user.id)} />
                { user.path_signature && <button className="button alert text-uppercase" onClick={() => this.deleteFileSignature(user.id)}>{t('common.button.delete')}</button>}
            </div>
        )
    }
}


SignatureForm.PropTypes = {
    user: object.isRequired,
    styleClass: string
}

export default translate(['sesile'])(SignatureForm)