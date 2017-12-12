import React, { Component } from 'react'
import { number, func } from 'prop-types'
import { translate } from 'react-i18next'
import { escapedValue } from '../_utils/Search'
import Select from 'react-select'

class SelectCollectivite extends Component {

    static contextTypes = {
        t: func 
    }

    static propTypes = {
        currentCollectiviteId: number,
        handleChange: func.isRequired
    }

    state = {
        collectivites: [],
        currentCollectivite: {}
    }

    componentDidMount() {
        fetch(Routing.generate('sesile_main_collectiviteapi_getall'), {credentials: 'same-origin'})
        .then(response => response.json())
        .then(json => this.setState({collectivites: json}))
        .then(() => {
            const currentCollectivite = this.state.collectivites.find(collectivite => collectivite.id === this.props.currentCollectiviteId) 
            this.setState({currentCollectivite})
        })
    }

    handleChange = (collectivite) => {
        this.setState({currentCollectivite: collectivite})
        if(!!collectivite) this.props.handleChange(collectivite.id)
    }

    render() {
        const { t } = this.context
        const { currentCollectivite, collectivites } = this.state
        const options = collectivites.map((collectivite, key) => <option key={key} value={collectivite.id} disabled={!collectivite.active ? true : false}>{collectivite.nom}</option>)
        return (
            <div >
                <label htmlFor="collectivites_select">{t('admin.label.which_collectivite')}</label>
                <Select id="collectivites_select" value={currentCollectivite} wrapperStyle={{marginBottom : "1em"}} valueKey="id" labelKey="nom" options={collectivites} onChange={this.handleChange} />
            </div>
        )
    }
}

export default translate(['sesile'])(SelectCollectivite)