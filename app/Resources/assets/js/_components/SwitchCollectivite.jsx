import React, { Component } from 'react'
import { number, func } from 'prop-types'
import { translate } from 'react-i18next'
import { escapedValue } from '../_utils/Search'
import Select from 'react-select'
import { Route, Redirect } from 'react-router-dom'

class SwitchCollectivite extends Component {

    static contextTypes = {
        t: func 
    }

    static propTypes = {
        currentCollectiviteId: number,
        handleChange: func.isRequired
    }

    state = {
        collectivites: [],
        currentCollectivite: {},
        redirect: false,
        redirectUrl: {}
    }

    handleChange = (collectivite) => {
        this.setState({currentCollectivite: collectivite})
        let host = window.location.host;
        let protocol = window.location.protocol;
        var url = protocol + '//' + collectivite.domain + '.' + host +'/connect/ozwillo';
        location = url;
        {/*<Route exact path="/" render={() => (*/}
        // return <Redirect to="url"/>
            {/*<Redirect to="/dashboard" push/>*/}
        {/*)}/>*/}

        //redirect to : http://sesile-dev.local/connect/ozwillo
        // if(!!collectivite) this.props.handleChange(collectivite.id)
    }

    render() {
        const { user } = this.props
        const { t } = this.context
        const { currentCollectivite, collectivites } = this.state
        return (
            <label htmlFor="collectivites_select">
                <span  className="text-bold">{t('admin.label.which_collectivite')}</span>
                <Select 
                    id="collectivites_select"
                    placeholder={t('admin.collectivite.select_collectivite')}
                    value={this.props.user.current_org_id}
                    wrapperStyle={{marginBottom : "0.65em"}} 
                    valueKey="id" 
                    labelKey="nom" 
                    options={this.props.user.collectivities}
                    onChange={this.handleChange} />
            </label>
        )
    }
}

export default translate(['sesile'])(SwitchCollectivite)