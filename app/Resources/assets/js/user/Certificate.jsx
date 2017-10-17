import React, { Component } from 'react'
import {func} from 'prop-types'
import { translate } from 'react-i18next'
import { basicNotification } from '../_components/Notifications'

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
        fetch(Routing.generate("sesile_user_userapi_getcurrent"), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => {
                this.setState({user: json})
            })
    }

    fetchCertificate() {
        fetch(Routing.generate("sesile_user_userapi_getcertificate"), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(certificate => {
                this.setState({certificate})
            })
    }

    render () {
        const { t } = this.context
        const { user, certificate } = this.state

        return (
        <div className="grid-x">
            <div className="admin-details medium-12 cell">
                <div className="grid-x admin-head-details">
                    {user._prenom + " " + user._nom + " - " + user.email}
                </div>
                <div className="admin-content-details">

                    <div className="grid-x grid-margin-x grid-padding-x">
                        <div className="medium-12 cell">
                            <h3>{t('common.user.certificate')}</h3>
                        </div>
                    </div>
                    <div className="grid-x grid-margin-x grid-padding-x">
                        <div className="medium-4 cell">
                            {t('common.user.certificate_serial')}
                        </div>
                        <div className="medium-8 cell">
                            2A841CF28C8DB0C294EF7B55C7B3390BC6A1F8E1
                        </div>
                    </div>
                    <div className="grid-x grid-margin-x grid-padding-x">
                        <div className="medium-4 cell">
                            {t('common.user.certificate_transmitter')}
                        </div>
                        <div className="medium-8 cell">
                            /C=FR/ST=Alpes-Maritimes/L=Vallauris/O=SICTIAM/CN=Certificats SICTIAM/emailAddress=internet@sictiam.fr
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
                                <div className="medium-6">Frédéric Laussinot</div>
                            </div>
                            <div className="grid-x">
                                <div className="medium-6">{t('common.user.organisation')}</div>
                                <div className="medium-6">SICTIAM</div>
                            </div>
                            <div className="grid-x">
                                <div className="medium-6">{t('common.user.organisation_unit')}</div>
                                <div className="medium-6">SICTIAM</div>
                            </div>
                            <div className="grid-x">
                                <div className="medium-6">{t('common.user.email')}</div>
                                <div className="medium-6">f.laussinot@sictiam.fr</div>
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
                                <div className="medium-6">Certificats SICTIAM</div>
                            </div>
                            <div className="grid-x">
                                <div className="medium-6">{t('common.user.organisation')}</div>
                                <div className="medium-6">SICTIAM</div>
                            </div>
                            <div className="grid-x">
                                <div className="medium-6">{t('common.user.email')}</div>
                                <div className="medium-6">internet@sictiam.fr</div>
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
                                <div className="medium-6">25/08/2016 à 15:14:34</div>
                            </div>
                            <div className="grid-x">
                                <div className="medium-6">{t('common.user.to')}</div>
                                <div className="medium-6">11/02/2019 à 14:14:34</div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    )
    }
}

export default translate(['sesile'])(Certificate)