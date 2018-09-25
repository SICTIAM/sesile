import React, {Component} from 'react'
import Classeurs from './Classeurs'
import { func, object } from 'prop-types'
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
        _addNotification: func,
        user: object
    }

    constructor(props) {
        super(props)
        this.state = {
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
        fetch(Routing.generate('sesile_classeur_classeurapi_list', {orgId: this.context.user.current_org_id, sort, order, limit, start}), { credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(lastClasseurs => {
                let messageLastClasseur = null
                if(lastClasseurs.list.length <= 0) messageLastClasseur = t('common.empty_list')
                this.setState({lastClasseurs: lastClasseurs.list, messageLastClasseur})
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
        fetch(Routing.generate('sesile_classeur_classeurapi_valid', {orgId: this.context.user.current_org_id, sort, order, limit, start}), { credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(urgentClasseurs => {
                let messageUrgentClasseur = null
                if(urgentClasseurs.list.length <= 0) messageUrgentClasseur = t('common.empty_list')
                this.setState({urgentClasseurs: urgentClasseurs.list, messageUrgentClasseur})
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
                                    <div className="align-middle" style={{paddingTop:'0.5em'}}>
                                        <h3>{t('common.user.certificate_info')}</h3>
                                    </div>
                                    <div className="grid-x grid-padding-x dashboard-title align-middle" style={{fontSize:"0.875em"}}>
                                        <CertificateValidity
                                            certificate={this.state.certificate}
                                            certificateRemainingDays={certificateRemainingDays}
                                            CertifRemain={t('common.user.certificate_validity', {count: certificateRemainingDays | 1})}
                                            NoCertif={t('common.no_certificat')}>
                                        </CertificateValidity>
                                        <div className="cell medium-6 small-6 text-right text-bold">
                                            <a href={"https://www.sictiam.fr/certificat-electronique/"} style={{textDecoration:"underline"}}>{t('common.button.certificate_order')}</a>
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