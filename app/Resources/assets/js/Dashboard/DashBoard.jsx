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
            urgentClasseurs: [],
            statsType: []
        }
    }

    componentDidMount() {
        this.fetchLastClasseurs()
        this.fetchUrgentClasseurs()
        this.fetchClasseursValidateByType()
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

    fetchClasseursValidateByType() {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_user_etapeclasseurapi_getclasseursvalidatebytype'), { credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(statsType => this.setState({statsType}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {name: t('admin.type.name'), errorCode: error.status}),
                error.statusText)))
    }

    render () {

        const { lastClasseurs, urgentClasseurs, statsType } = this.state
        const { t } = this.context

        return (
            <div className="grid-x">

                <div className="cell medium-12">
                    <div className="grid-x grid-margin-x grid-padding-x align-top align-center">
                        <div className="cell medium-5">
                            <Classeurs classeurs={ lastClasseurs } title={ t('common.dashboard.last_classeurs')} />
                        </div>

                        <div className="cell medium-5">
                            <Classeurs classeurs={ urgentClasseurs } title={ t('common.dashboard.urgent_classeurs')} />

                            <div className="grid-x grid-padding-x panel list-dashboard">
                                <div className="cell medium-12 panel-heading">
                                    { t('common.user.certificate_info')}
                                </div>
                                <div className="cell medium-12 panel-body">
                                    <div className="grid-x align-middle">
                                        <div className="cell medium-8 text-bold">
                                            { t('common.user.certificate_validity', {count: 5}) }
                                        </div>
                                        <div className="cell medium-4 text-justify">
                                            <Link className="button primary" to="https://www.sictiam.fr/certificat-electronique/" target="_blank">{ t('common.button.certificate_order') }</Link>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div className="grid-x grid-padding-x panel list-dashboard">
                                <div className="cell medium-12 panel-heading">
                                    { t('common.dashboard.classeurs_add')}
                                </div>
                                <div className="cell medium-12 panel-body text-center">
                                    <Link className="button primary" to="#">{ t('common.classeurs.button.add_classeur') }</Link>
                                </div>
                            </div>

                            <div className="grid-x grid-padding-x panel list-dashboard">
                                <div className="cell medium-12 panel-heading">
                                    { t('common.dashboard.stats_classeurs_validate_by_type')}
                                </div>
                                <div className="cell medium-12 panel-body-no-padding">
                                    <Chart
                                        chartType="PieChart"
                                        data={statsType}
                                        options={{"is3D":true,"fontSize":14}}
                                        graph_id="statsType"
                                        width="100%"
                                        height="300px"
                                        legend_toggle
                                    />
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