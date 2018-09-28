import React, { Component } from 'react'
import { func } from 'prop-types'
import { basicNotification } from "../_components/Notifications"
import { handleErrors } from '../_utils/Utils'

class VideoCont extends Component {

    static contextTypes = {
        t: func
    }

    constructor(props) {
        super(props);
        this.state = {
            id_yt_video: ''
        }
    }

    fetchVideo = () => {
        fetch(Routing.generate('sesile_main_default_getappinformation'), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(id => this.setState({ id_yt_video: id.id_yt_video }))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {name: t('common.help_board.title_helps'), errorCode: error.status}),
                error.statusText)))
    }

    componentDidMount() {
        this.fetchVideo()
    }

    render () {
        const {t} = this.context
        const { id_yt_video } = this.state
        let video;

        if(id_yt_video) {
            video =  <iframe width="560" height="315"
                             src={`https://www.youtube.com/embed/${id_yt_video}?rel=0&amp;controls=0&amp;showinfo=0`}
                             frameBorder="0" allow="autoplay; encrypted-media" allowFullScreen></iframe>
        }
        return(
            <div className="cell medium-10">
                <div className="panel cell medium-10" style={{padding:"1em", textAlign:"center"}}>
                    <h3 style={{textAlign:"left"}}>{t('common.help_board.title_video')}</h3>
                    {video}
                </div>
            </div>
        )
    }
}
export default VideoCont