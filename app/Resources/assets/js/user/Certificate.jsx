import React, { Component } from 'react'
import {func} from 'prop-types'
import { translate } from 'react-i18next'
import { basicNotification } from '../_components/Notifications'
import { handleErrors } from '../_utils/Utils'
import Moment from 'moment'
import {AdminPage, SimpleContent} from "../_components/AdminUI";
import {Form} from "../_components/Form";

class Certificate extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }

    constructor(props) {
        super(props)
        this.state = {
            certificate: {},
            user: {
                _nom: '',
                _prenom: ''
            }
        }
    }

    componentDidMount() {
        this.fetchUser()
        this.fetchCertificate()
    }

    fetchUser() {
        const {t, _addNotification} = this.context
        fetch(Routing.generate("sesile_user_userapi_getcurrent"), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(user => this.setState({user}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {name: t('admin.user.name'), errorCode: error.status}),
                error.statusText)))
    }

    fetchCertificate() {
        fetch(Routing.generate("sesile_user_userapi_getcertificate"), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(certificate => this.setState({certificate}))
    }

    render() {
        const {t} = this.context
        const {user, certificate} = this.state

        return (
            <AdminPage>
                <div className="cell medium-12 text-center">
                    <h2>{t('common.user.certificate')}</h2>
                </div>
                <SimpleContent className="panel">
                    <div className="grid-x grid-margin-x grid-padding-x">
                        <div className="medium-12 cell">
                            <h3>Informations</h3>
                        </div>
                    </div>
                    <div className="grid-x grid-margin-x grid-padding-x">
                        <div className="medium-4 cell">
                            {t('common.user.certificate_serial')}
                        </div>
                        <div className="medium-8 cell">
                            {certificate.HTTP_X_SSL_CLIENT_M_SERIAL}
                        </div>
                    </div>
                    <div className="grid-x grid-margin-x grid-padding-x">
                        <div className="medium-4 cell">
                            {t('common.user.certificate_transmitter')}
                        </div>
                        <div className="medium-8 cell">
                            {certificate.HTTP_X_SSL_CLIENT_I_DN}
                        </div>
                    </div>

                    <div className="grid-x grid-margin-x grid-padding-x">
                        <div className="medium-4 cell">
                            <div className="grid-x">
                                <div className="medium-12 text-center">
                                    <h3>{t('common.user.transmitter_for')}</h3>
                                </div>
                            </div>
                            <div className="grid-x">
                                <div className="medium-6">{t('common.user.common_name')}</div>
                                <div className="medium-6">{certificate.HTTP_X_SSL_CLIENT_S_DN_CN}</div>
                            </div>
                            <div className="grid-x">
                                <div className="medium-6">{t('common.user.organisation')}</div>
                                <div className="medium-6">{certificate.HTTP_X_SSL_CLIENT_S_DN_O}</div>
                            </div>
                            <div className="grid-x">
                                <div className="medium-6">{t('common.user.organisation_unit')}</div>
                                <div className="medium-6">{certificate.HTTP_X_SSL_CLIENT_S_DN_OU}</div>
                            </div>
                            <div className="grid-x">
                                <div className="medium-6">{t('common.user.email')}</div>
                                <div className="medium-6">{certificate.HTTP_X_SSL_CLIENT_S_DN_EMAIL}</div>
                            </div>
                        </div>

                        <div className="medium-4 cell">
                            <div className="grid-x">
                                <div className="medium-12 text-center">
                                    <h3>{t('common.user.transmitter_from')}</h3>
                                </div>
                            </div>
                            <div className="grid-x">
                                <div className="medium-6">{t('common.user.common_name')}</div>
                                <div className="medium-6">{certificate.HTTP_X_SSL_CLIENT_I_DN_CN}</div>
                            </div>
                            <div className="grid-x">
                                <div className="medium-6">{t('common.user.organisation')}</div>
                                <div className="medium-6">{certificate.HTTP_X_SSL_CLIENT_I_DN_O}</div>
                            </div>
                            <div className="grid-x">
                                <div className="medium-6">{t('common.user.email')}</div>
                                <div className="medium-6">{certificate.HTTP_X_SSL_CLIENT_I_DN_EMAIL}</div>
                            </div>
                        </div>

                        <div className="medium-4 cell">
                            <div className="grid-x">
                                <div className="medium-12 text-center">
                                    <h3>{t('common.user.validity')}</h3>
                                </div>
                            </div>
                            <div className="grid-x">
                                <div className="medium-6">{t('common.user.from')}</div>
                                <div
                                    className="medium-6">{Moment(certificate.HTTP_X_SSL_CLIENT_NOT_BEFORE).format('LL')}</div>
                            </div>
                            <div className="grid-x">
                                <div className="medium-6">{t('common.user.to')}</div>
                                <div
                                    className="medium-6">{Moment(certificate.HTTP_X_SSL_CLIENT_NOT_AFTER).format('LL')}</div>
                            </div>
                        </div>

                    </div>
                </SimpleContent>
            </AdminPage>
        )
    }
}

export default translate(['sesile'])(Certificate)