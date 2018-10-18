import React, { Component } from 'react'
import { Link } from 'react-router-dom'
import { func } from 'prop-types'
import { translate } from 'react-i18next'

class AppInfos extends Component {
    
    static contextTypes = {
        t: func
    }

    state = {
        informations: {
            version: 0,
            contact_link: ''
        }
    }

    componentDidMount() {
        fetch(Routing.generate('sesile_main_default_getappinformation'), {credentials: 'same-origin'})
        .then(response => response.json())
        .then(json => this.setState({informations: json}))
    }
    
    render() {
        const { t } = this.context
        return (
            <div className="cell app-infos text-uppercase" style={{width:"70%", marginLeft:"15%"}}>
                <div className="grid-x align-center shrink align-self-bottom">
                    <div className="cell medium-8"><img src="/images/logo_sictiam.svg" /></div>
                </div>
                <div className="grid-x align-center">
                    <div className="cell medium-12">
                        <a href={this.state.informations.contact_link} target="_blank">{t('footer.contact')}</a>
                    </div>
                </div>
                <div className="grid-x align-center">
                    <div className="cell medium-12">
                        <Link to="">{t('footer.general_conditions')}</Link>
                    </div>
                </div>
                <div className="grid-x align-center">
                    <div className="cell medium-12">
                        <Link to="/documentations">{t('footer.version', {version: this.state.informations.version})}</Link>
                    </div>
                </div>
                <div className="grid-x align-center">
                    <div className="cell medium-12">{t('footer.love')}</div>
                </div>
            </div>
        )
    }
}

export default translate(['sesile'])(AppInfos)