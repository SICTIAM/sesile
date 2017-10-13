import React, { Component } from 'react'
import { number, func } from 'prop-types'
import { translate } from 'react-i18next'
import { escapedValue } from '../_utils/Search'

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
        filteredCollectivites: [],
        currentCollectiviteNom: '',
        currentCollectiviteId: 0
    }

    componentDidMount() {
        this.fetchCollectivites()
    }
    
    fetchCollectivites() {
        fetch(Routing.generate('sesile_main_collectiviteapi_getall'), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => {
                this.setState({collectivites: json})
                this.HandleClick(this.props.currentCollectiviteId)
            })
    }

    HandleClick = (currentCollectiviteId) => {
        const currentCollectivite = this.state.collectivites.find(collectivite => collectivite.id === currentCollectiviteId)
        this.setState({ filteredCollectivites: [], currentCollectiviteNom: currentCollectivite.nom})
        this.props.handleChange(currentCollectivite.id)
    }

    handleChange = (value) => {
        this.setState({currentCollectiviteNom: value})
        if(!!value) {
            const regex = escapedValue(value, this.state.filteredCollectivites, this.state.collectivites, false)
            const filteredCollectivites = this.state.collectivites.filter(collectivite => regex.test(collectivite.nom))
            this.setState({filteredCollectivites})
        } else {
            this.setState({filteredCollectivites: []})
        }
    }

    render() {
        const { t } = this.context
        const { filteredCollectivites, currentCollectiviteNom } = this.state
        return (
            <div className="autocomplete">
                <label htmlFor="collectivites-select">{t('admin.label.which_collectivite')}</label>
                <input  className="input-autocomplete" 
                        value={currentCollectiviteNom} 
                        type={"text"}
                        placeholder={t('admin.collectivite.type_name')} 
                        onChange={(e) => this.handleChange(e.target.value)}/>
                {filteredCollectivites.length > 0 &&
                    <ListCollectivite filteredCollectivites={filteredCollectivites} HandleClick={this.HandleClick} />
                }
            </div>
        )
    }
}

export default translate(['sesile'])(SelectCollectivite)

const ListCollectivite = ({filteredCollectivites, HandleClick}) => {
    const options = filteredCollectivites.map((collectivite, key) => {
        if(collectivite.active && key < 10) { return <li key={key} className="list-group-item" value={collectivite.id} onClick={(e) => HandleClick(e.target.value)}>{collectivite.nom}</li> }
    })
    return(
        <div className="list-autocomplete">
            <ul className="list-group">
                {options}
            </ul>
        </div>
    )
}