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
            <div className="cell medium-12 align-self-bottom app-infos text-uppercase show-for-medium">
                <div className="grid-x align-center">
                    <div className="cell medium-8"><img src="/images/logo_sictiam.png" /></div>
                </div>
                <div className="">
                    <a href={this.state.informations.contact_link} target="_blank">{t('footer.contact')}</a>
                </div>
                <div className="">
                    <Link to="">{t('footer.general_conditions')}</Link>
                </div>
                <div className="">
                    <Link to="/documentations">{t('footer.version', {version: this.state.informations.version})}</Link>
                </div>
                <div className="align-center">
                    <a href="https://www.sictiam.fr" target="_blank">{t('footer.love')}</a>
                </div>
            </div>
        )
    }
}

export default translate(['sesile'])(AppInfos)