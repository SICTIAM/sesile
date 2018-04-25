import React, { Component } from 'react'
import { array, func } from 'prop-types'
import Debounce from 'debounce'

class SearchUser extends Component {
    
    state = {
        users: [],
        inputDisplayed: false,
        inputSearchUser: ''
    }

    findUser = Debounce((value, collectiviteId) => {
        fetch(Routing.generate("sesile_user_userapi_findbynomorprenom", {value, collectiviteId}), {credentials: 'same-origin'})
        .then(response => response.json())
        .then(json => {
            let users = json
            this.setState({users})
        })
    }, 800)

    handleChangeSearchUser = (value) => {
        this.setState({inputSearchUser: value})
        if(value.trim().length > 2) this.findUser(value, this.props.collectiviteId)
        else this.setState({users: []})
    }

    render() {
        const { inputSearchUser, users } = this.state
        return (
            <div className="autocomplete">
                <input value={inputSearchUser} type={"text"} onChange={(e) => this.handleChangeSearchUser(e.target.value)} className="input-autocomplete"></input>
                {users.length > 0 &&
                    <ListSearchUser users={users} onClick={this.handleClickUser} />
                }
            </div>
        )
    }
}

export default SearchUser

const ListSearchUser = ({ users, onClick }) => {
    const list = users.map((user, key) => <li className="list-group-item" onClick={() => onClick(user)} key={key}>{user._prenom + ' ' + user._nom}</li>)
    return (
        <div className="list-autocomplete">
            <ul className="list-group">
                {list}
            </ul>
        </div>
    )
}

ListSearchUser.PropTypes = {
    users: array.isRequired,
    onClick: func.isRequired
}