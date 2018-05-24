import React, { Component } from 'react'
import { Chart } from 'react-google-charts'
import { translate } from 'react-i18next'
import {handleErrors} from '../_utils/Utils'
import {basicNotification} from '../_components/Notifications'
import { func } from 'prop-types'

class Stats extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }

    constructor(props) {
        super(props)
        this.state = {
            statsType: []
        }
    }

    componentDidMount() {
        this.fetchClasseursValidateByType()
    }

    fetchClasseursValidateByType() {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_user_etapeclasseurapi_getclasseursvalidatebytype', {orgId: this.props.user.current_org_id}), { credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(statsType => this.setState({statsType}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {name: t('admin.type.name'), errorCode: error.status}),
                error.statusText)))
    }

    render () {

        const { statsType } = this.state
        const { t } = this.context

        return (
            <div className="grid-x align-center">

                <div className="cell medium-12">
                    <div className="grid-x grid-margin-x grid-padding-x align-top align-center grid-padding-y">
                        <div className="cell medium-12 text-center">
                            <h1>{ t('common.menu.stats')}</h1>
                        </div>
                    </div>


                    <div className="grid-x grid-margin-x grid-padding-x grid-padding-y">
                        <div className="cell medium-12">
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

export default translate('sesile')(Stats)