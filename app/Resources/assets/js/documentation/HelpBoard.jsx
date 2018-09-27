import React, { Component } from 'react'
import { func } from 'prop-types'
import { translate } from 'react-i18next'
import { AdminDetails, AccordionContent, AccordionItem} from "../_components/AdminUI";
import Patchs from "./Patchs";
import Helps from "./Helps";

class HelpBoard extends Component {

    static contextTypes = {
        t: func
    }

    componentDidMount() {
        $("#admin-details").foundation()
    }

    render() {
        const { t } = this.context

        return (
            <div>
                <div className="grid-x grid-margin-x grid-padding-x align-top align-center grid-padding-y">
                    <div className="cell medium-12 text-center">
                        <h2>{t('common.help_board.title')}</h2>
                    </div>
                </div>
                <div className="grid-x grid-margin-x grid-padding-x grid-padding-y">
                    <div className="cell medium-12">
                        <div className="grid-x grid-padding-x list-dashboard align-center">
                            <VideoContent />
                            <Helps />
                            <Patchs />
                        </div>
                    </div>
                </div>
            </div>
        )
    }

}

export default translate(['sesile'])(HelpBoard)



const VideoContent = ({}, {t}) => {

    return(
        <div className="cell medium-10">
            <div className="panel cell medium-10" style={{padding:"1em", textAlign:"center"}}>
                <h3 style={{textAlign:"left"}}>{t('common.help_board.title_video')}</h3>
                <iframe width="560" height="315"
                        src="https://www.youtube.com/embed/C72d6DJBkgw?rel=0&amp;controls=0&amp;showinfo=0"
                        frameBorder="0" allow="autoplay; encrypted-media" allowFullScreen></iframe>
            </div>
        </div>
    )
}
VideoContent.contextTypes = {
    t: func
}