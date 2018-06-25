import React, { Component } from 'react'
import { func } from 'prop-types'
import Select from 'react-select'
import { translate } from 'react-i18next'

import { handleErrors } from './_utils/Utils'

class SelectCollectivite extends Component {
    static contextTypes = {
        t: func
    }
    state = {
        currentCollectivite: {},
        collectivites: []
    }
    componentDidMount() {
        fetch(Routing.generate("sesile_main_collectiviteapi_getorganisationlist"), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(collectivites => {
                this.setState({collectivites})
            })
            // @todo modify translation of the notification
            .catch(error => this.context._addNotification(basicNotification(
                'error',
                this.context.t('classeur.error.edit', {errorCode: error.status}),
                error.statusText)))
    }
    handleChange = (currentCollectivite) => this.setState({currentCollectivite})
    render() {
        const { t } = this.context
        const redirectLoginUrl =
            this.state.currentCollectivite.domain ?
                Routing.generate(
                    "sesile_main_default_redirecttosubdomain",
                    {subdomain: this.state.currentCollectivite.domain}) :
                '#'
        return (
            <div className="grid-container main">
                <div className="grid-x grid-margin-x grid-padding-y align-center-middle"
                     style={{marginTop: '5em'}}>
                    <div
                        className="cell medium-4">
                        <div className="grid-x grid-padding-y grid-margin-y">
                            <div className="cell medium-12 text-center">
                                <h2 style={{textTransform: 'none'}}>
                                    {t('common.select_collectivite')}
                                </h2>
                            </div>
                        </div>
                        <div
                            className="grid-x grid-margin-x grid-margin-y align-center-middle"
                            style={{
                                backgroundColor: 'white',
                                boxShadow: '0 1px 2px 0 rgba(34, 36, 38, 0.15)',
                                borderRadius: '0.28571429rem',
                                border: '1px solid rgba(34, 36, 38, 0.15'}}>
                            <div className='cell medium-12'>
                                <Select id="collectivite-select"
                                        value={this.state.currentCollectivite}
                                        placeholder={t('common.research')}
                                        valueKey="domain"
                                        labelKey="nom"
                                        clearable={false}
                                        options={this.state.collectivites}
                                        onChange={this.handleChange}/>
                            </div>
                            <div className="cell medium-12">
                                <a
                                    href={redirectLoginUrl}
                                    className="button primary hollow"
                                    disabled={this.state.currentCollectivite.domain === undefined}
                                    style={{width: '100%'}}>
                                    Connexion
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}

export default translate(['sesile'])(SelectCollectivite)