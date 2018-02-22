import React, {Component} from 'react'
import Classeurs from './Classeurs'
import { func } from 'prop-types'
import { Link } from 'react-router-dom'
import { translate } from 'react-i18next'
import { handleErrors } from '../_utils/Utils'
import { Chart } from 'react-google-charts'
import { basicNotification } from '../_components/Notifications'

class DashBoard extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }

    constructor(props) {
        super(props)
        this.state = {
            lastClasseurs: [],
            urgentClasseurs: []
        }
    }

    componentDidMount() {
        this.fetchLastClasseurs()
        this.fetchUrgentClasseurs()
    }

    fetchLastClasseurs() {
        const sort = 'creation'
        const order = 'DESC'
        const limit = 10
        const start = 0
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_classeur_classeurapi_list', {sort, order, limit, start}), { credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(lastClasseurs => this.setState({lastClasseurs}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {name: t('common.classeurs.name'), errorCode: error.status}),
                error.statusText)))
    }

    fetchUrgentClasseurs() {
        const sort = 'validation'
        const order = 'ASC'
        const limit = 5
        const start = 0
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_classeur_classeurapi_valid', {sort, order, limit, start}), { credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(urgentClasseurs => this.setState({urgentClasseurs}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {name: t('common.classeurs.name'), errorCode: error.status}),
                error.statusText)))
    }


    render () {

        const { lastClasseurs, urgentClasseurs } = this.state
        const { t } = this.context

        return (
            <div className="grid-x">

                <div className="cell medium-12">
                    <div className="grid-x grid-margin-x grid-padding-x align-top align-center grid-padding-y">
                        <div className="cell medium-12 text-center">
                            <h1>{ t('common.dashboard.title') }</h1>
                        </div>
                    </div>
                    <div className="grid-x grid-margin-x grid-padding-x align-top align-center">
                        <div className="cell medium-5">
                            <Classeurs classeurs={ lastClasseurs } title={ t('common.dashboard.last_classeurs')} />
                        </div>

                        <div className="cell medium-5">
                            <Classeurs classeurs={ urgentClasseurs } title={ t('common.dashboard.urgent_classeurs')} />

                            <div className="grid-x grid-padding-x panel">
                                <div className="cell medium-12">
                                    <div className="grid-x panel-heading grid-padding-x align-middle">
                                        <div className="cell medium-12">{ t('common.user.certificate_info')}</div>
                                    </div>
                                    <div className="grid-x panel-body grid-padding-x dashboard-title align-middle">
                                        <div className="cell medium-8 text-bold">
                                            { t('common.user.certificate_validity', {count: 5}) }
                                        </div>
                                        <div className="cell medium-4 text-justify">
                                            <Link className="button primary hollow" to="https://www.sictiam.fr/certificat-electronique/" target="_blank">{ t('common.button.certificate_order') }</Link>
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