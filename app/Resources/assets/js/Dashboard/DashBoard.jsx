import React, {Component} from 'react'
import Classeurs from './Classeurs'
import { func } from 'prop-types'
import { Link } from 'react-router-dom'
import { translate } from 'react-i18next'
import { handleErrors } from '../_utils/Utils'
import { Chart } from 'react-google-charts'
import { basicNotification } from '../_components/Notifications'
import Moment from 'moment'
import { CertificateValidity } from '../_components/CertificateExpiry'

class DashBoard extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }

    constructor(props) {
        super(props)
        this.state = {
            currentOrgId : this.props.user.current_org_id,
            lastClasseurs: [],
            urgentClasseurs: [],
            certificate: null,
            messageLastClasseur: null,
            messageUrgentClasseur: null
        }
    }

    componentDidMount() {
        this.fetchLastClasseurs()
        this.fetchUrgentClasseurs()
        this.fetchCertificate()
    }

    fetchCertificate() {
        fetch(Routing.generate("sesile_user_userapi_getcertificate"), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(certificate => this.setState({certificate}))
    }

    fetchLastClasseurs() {
        const sort = 'creation'
        const order = 'DESC'
        const limit = 10
        const start = 0
        const { t, _addNotification } = this.context
        this.setState({messageLastClasseur: t('common.loading')})
        fetch(Routing.generate('sesile_classeur_classeurapi_list', {orgId: this.state.currentOrgId, sort, order, limit, start}), { credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(lastClasseurs => {
                let messageLastClasseur = null
                if(lastClasseurs.length <= 0) messageLastClasseur = t('common.empty_list')
                this.setState({lastClasseurs, messageLastClasseur})
            })
            .catch(() => this.setState({messageLastClasseur: t('common.error_loading_list')}))
    }

    fetchUrgentClasseurs() {
        const sort = 'validation'
        const order = 'ASC'
        const limit = 5
        const start = 0
        const { t, _addNotification } = this.context
        this.setState({messageUrgentClasseur: t('common.loading')})
        fetch(Routing.generate('sesile_classeur_classeurapi_valid', {orgId: this.state.currentOrgId, sort, order, limit, start}), { credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(urgentClasseurs => {
                let messageUrgentClasseur = null
                if(urgentClasseurs.length <= 0) messageUrgentClasseur = t('common.empty_list')
                this.setState({urgentClasseurs, messageUrgentClasseur})
            })
            .catch(() => this.setState({messageUrgentClasseur: t('common.error_loading_list')}))
    }


    render () {

        const { lastClasseurs, urgentClasseurs } = this.state
        const { t } = this.context
        if (this.state.certificate && this.state.certificate.HTTP_X_SSL_CLIENT_NOT_AFTER) {
            var now = Moment();
            var end = Moment(this.state.certificate.HTTP_X_SSL_CLIENT_NOT_AFTER);
            var certificateRemainingDays = end.diff(now, 'days');
        }

        return (
            <div className="grid-x">
                <div className="cell medium-12">
                    <div className="grid-x grid-margin-x grid-padding-x align-top align-center grid-padding-y">
                        <div className="cell medium-12 text-center">
                            <h2>{ t('common.dashboard.title') }</h2>
                        </div>
                    </div>
                    <div className="grid-x grid-margin-x grid-padding-x align-top align-center">
                        <div className="cell large-6 medium-12">
                            <Classeurs classeurs={lastClasseurs} message={this.state.messageLastClasseur} title={t('common.dashboard.last_classeurs')} />
                        </div>
                        <div className="cell large-6 medium-12">
                            <Classeurs classeurs={urgentClasseurs} message={this.state.messageUrgentClasseur} title={t('common.dashboard.urgent_classeurs')} />
                            <div className="grid-x grid-padding-x panel">
                                <div className="cell medium-12">
                                    <div className="grid-x panel-heading grid-padding-x align-middle">
                                        <div className="cell medium-12 text-center medium-text-left">{ t('common.user.certificate_info')}</div>
                                    </div>
                                    <div className="grid-x panel-body grid-padding-x dashboard-title align-middle">
                                        <CertificateValidity
                                            certificate={this.state.certificate}
                                            certificateRemainingDays={certificateRemainingDays}
                                            CertifRemain={t('common.user.certificate_validity', {count: certificateRemainingDays})}
                                            NoCertif={t('common.no_certificat')}>
                                        </CertificateValidity>
                                        <div className="cell medium-auto small-12 text-center medium-text-left text-bold">
                                            <Link
                                                className="button float-left text-uppercase hollow"
                                                to="https://www.sictiam.fr/certificat-electronique/"
                                                target="_blank">
                                                {t('common.button.certificate_order')}
                                            </Link>
                                        </div>
                                        <div className="cell medium-auto small-12 text-center medium-text-right">
                                            {this.state.certificate &&
                                            <Link
                                                className="button text-uppercase hollow"
                                                to="/utilisateur/certificat-electronique">
                                                {t('common.button.certificate_user')}
                                            </Link>}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}

export default translate(['sesile'])(DashBoard)