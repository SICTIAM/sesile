import React, { Component } from 'react'
import { func } from 'prop-types'
import Autosuggest from 'react-autosuggest'
import Debounce from 'debounce'
import { escapedValue } from '../_utils/Search'

class SearchUserAndGroup extends Component {

    static contextTypes = {
        t: func
    }
    
    state = {
        value: '',
        suggestions: [  { title: 'Groupes', usersOrGroups: [] },
                        { title: 'Utilisateurs', usersOrGroups: [] }],
        groups: []
    }

    componentDidMount() {
        this.fetchGroups(this.props.collectiviteId)
    }
    
    handleChange = (event, { newValue }) => this.setState({value: newValue})
    
    setSuggestions (sectionName, results) {
        let suggestions = this.state.suggestions
        suggestions = suggestions.map(section => { 
            if(section.title === sectionName) return { title: section.title, usersOrGroups: results } 
            else return section
        })
        this.setState({suggestions})
    }

    findUser = Debounce((value) => {
        fetch(Routing.generate('sesile_user_userapi_findbynomorprenom', {value, collectiviteId: this.props.collectiviteId}), {credentials: 'same-origin'})
        .then(response => response.json())
        .then(json => this.setSuggestions('Utilisateurs', json.filter(userReceived => !this.props.step.users.find(user => userReceived.id === user.id))))
    } , 500, true)

    findGroup = value => {
        const { groups } = this.state
        const regex = escapedValue(value, groups)
        groups.length !== 0 && this.setSuggestions('Groupes', groups.filter(group => regex.test(group.nom) && !this.props.step.user_packs.find(user_pack => group.id === user_pack.id)))
    }

    fetchGroups = (collectiviteId) => {
        fetch(Routing.generate('sesile_user_userpackapi_getbycollectivite', {collectiviteId}), {credentials: 'same-origin'})
        .then(response => response.json())
        .then(json => this.setState({groups: json}))
    }

    getSuggestionValue = suggestion => suggestion && (suggestion.users ? suggestion.nom : suggestion._prenom + " " + suggestion._nom)

    renderSuggestion = suggestion =>
        <span>
            {suggestion.users ? suggestion.nom : suggestion._prenom + " " + suggestion._nom}
        </span>

    renderSectionTitle = section =>
        <div>
            <strong>
                {section.title}
            </strong>
            {section.usersOrGroups.length == 0 &&
                <p>{this.context.t('common.small_no_results')}</p>
            }
        </div>

    getSectionSuggestions = section => section.usersOrGroups

    onSuggestionsFetchRequested = ({value}) => {
        this.findUser(value)
        this.findGroup(value)
    }
    
    onSuggestionsClearRequested = () => this.setState(prevState =>  {suggestions: prevState.suggestions.map(section => section.usersOrGroups = [])})

    onSuggestionSelected = (event, { suggestion }) => {
        this.setState({value: ''})
        if(suggestion.users) this.props.addGroup(this.props.stepKey, suggestion)
        else this.props.addUser(this.props.stepKey, suggestion)
    }
    
    render() {
        const { value, suggestions } = this.state
        const inputProps = {
            placeholder: this.props.placeholder,
            value,
            onChange: this.handleChange
        }

        return (
            <Autosuggest    id={this.props.id}
                            multiSection={true}
                            suggestions={suggestions}
                            onSuggestionsFetchRequested={this.onSuggestionsFetchRequested}
                            onSuggestionsClearRequested={this.onSuggestionsClearRequested}
                            onSuggestionSelected={this.onSuggestionSelected}
                            getSuggestionValue={this.getSuggestionValue}
                            renderSuggestion={this.renderSuggestion}
                            renderSectionTitle={this.renderSectionTitle}
                            getSectionSuggestions={this.getSectionSuggestions}
                            inputProps={inputProps}
                            highlightFirstSuggestion={true}/>
        )
    }
}

export default SearchUserAndGroup